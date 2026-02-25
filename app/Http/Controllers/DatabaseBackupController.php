<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;

class DatabaseBackupController extends Controller
{
    public function download(Request $request): Response
    {
        $connectionName = config('database.default');
        $connection = (array) config("database.connections.{$connectionName}");
        $driver = (string) data_get($connection, 'driver', '');

        if ($driver === '') {
            abort(500, 'Database driver could not be determined.');
        }

        $databaseName = (string) data_get($connection, 'database', 'database');
        $safeDatabaseName = Str::slug(basename($databaseName), '_') ?: 'database';
        $timestamp = now()->format('Ymd_His');

        try {
            return match ($driver) {
                'mysql', 'mariadb' => $this->downloadMysqlDump($connection, "{$safeDatabaseName}_{$timestamp}.sql"),
                'pgsql' => $this->downloadPostgresDump($connection, "{$safeDatabaseName}_{$timestamp}.sql"),
                'sqlite' => $this->downloadSqliteFile($connection, "{$safeDatabaseName}_{$timestamp}.sqlite"),
                default => throw new RuntimeException("Unsupported database driver: {$driver}"),
            };
        } catch (\Throwable $exception) {
            return response(
                'Database export failed: ' . $exception->getMessage(),
                500,
                ['Content-Type' => 'text/plain; charset=UTF-8']
            );
        }
    }

    private function downloadMysqlDump(array $connection, string $downloadName): Response
    {
        $binary = $this->resolveCommandBinary('mysqldump');

        $connectionName = (string) config('database.default');
        $database = (string) data_get($connection, 'database', '');
        if ($database === '') {
            throw new RuntimeException('MySQL database name is missing.');
        }

        $temporaryDumpPath = $this->makeTemporaryDumpPath('sql');

        $environment = [];
        $password = (string) data_get($connection, 'password', '');
        if ($password !== '') {
            $environment['MYSQL_PWD'] = $password;
        }

        $socketPath = $this->resolveMysqlSocket($connection);
        $attemptCommands = [];
        if ($socketPath !== null) {
            $attemptCommands[] = $this->buildMysqlDumpCommand($binary, $connection, $temporaryDumpPath, $database, $socketPath);
        }
        $attemptCommands[] = $this->buildMysqlDumpCommand($binary, $connection, $temporaryDumpPath, $database, null, (string) data_get($connection, 'host', '127.0.0.1'));
        $attemptCommands[] = $this->buildMysqlDumpCommand($binary, $connection, $temporaryDumpPath, $database, null, 'localhost');
        $attemptCommands[] = $this->buildMysqlDumpCommand($binary, $connection, $temporaryDumpPath, $database, null, '127.0.0.1');

        $attemptCommands = array_map('unserialize', array_unique(array_map('serialize', $attemptCommands)));
        $lastErrorMessage = null;
        try {
            foreach ($attemptCommands as $command) {
                try {
                    $this->runProcess($command, $environment);
                    return response()
                        ->download($temporaryDumpPath, $downloadName, ['Content-Type' => 'application/sql; charset=UTF-8'])
                        ->deleteFileAfterSend(true);
                } catch (\Throwable $attemptException) {
                    $lastErrorMessage = $attemptException->getMessage();
                }
            }
        } catch (\Throwable $exception) {
            $lastErrorMessage = $exception->getMessage();
        }

        if (is_file($temporaryDumpPath)) {
            @unlink($temporaryDumpPath);
        }

        return $this->downloadMysqlDumpViaConnection($connectionName, $database, $downloadName, $lastErrorMessage);
    }

    private function downloadPostgresDump(array $connection, string $downloadName): Response
    {
        $binary = $this->resolveCommandBinary('pg_dump');

        $database = (string) data_get($connection, 'database', '');
        if ($database === '') {
            throw new RuntimeException('PostgreSQL database name is missing.');
        }

        $temporaryDumpPath = $this->makeTemporaryDumpPath('sql');

        $command = [
            $binary,
            '--format=plain',
            '--no-owner',
            '--no-privileges',
            '--host=' . (string) data_get($connection, 'host', '127.0.0.1'),
            '--port=' . (string) data_get($connection, 'port', '5432'),
            '--username=' . (string) data_get($connection, 'username', ''),
            '--file=' . $temporaryDumpPath,
            $database,
        ];

        $environment = [];
        $password = (string) data_get($connection, 'password', '');
        if ($password !== '') {
            $environment['PGPASSWORD'] = $password;
        }

        $this->runProcess($command, $environment);

        return response()
            ->download($temporaryDumpPath, $downloadName, ['Content-Type' => 'application/sql; charset=UTF-8'])
            ->deleteFileAfterSend(true);
    }

    private function downloadSqliteFile(array $connection, string $downloadName): BinaryFileResponse
    {
        $database = (string) data_get($connection, 'database', '');
        if ($database === '') {
            throw new RuntimeException('SQLite database path is missing.');
        }

        if ($database === ':memory:') {
            throw new RuntimeException('In-memory SQLite databases cannot be exported to file.');
        }

        if (!str_starts_with($database, DIRECTORY_SEPARATOR)) {
            $database = database_path($database);
        }

        if (!File::exists($database)) {
            throw new RuntimeException('SQLite database file not found.');
        }

        return response()->download($database, $downloadName);
    }

    private function resolveCommandBinary(string $command): string
    {
        $candidates = [
            '/opt/lampp/bin/' . $command,
            '/usr/bin/' . $command,
            '/usr/local/bin/' . $command,
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        $lookupCommand = PHP_OS_FAMILY === 'Windows'
            ? ['where', $command]
            : ['sh', '-lc', "command -v {$command}"];

        $process = new Process($lookupCommand);
        $process->run();
        if ($process->isSuccessful()) {
            $resolved = trim($process->getOutput());
            if ($resolved !== '') {
                return $resolved;
            }
        }

        throw new RuntimeException("Required command is not available: {$command}");
    }

    private function runProcess(array $command, array $environment = []): void
    {
        $process = new Process($command, null, $environment, null, 600);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(trim($process->getErrorOutput()) ?: 'Unknown process failure.');
        }
    }

    private function makeTemporaryDumpPath(string $extension): string
    {
        $path = tempnam(sys_get_temp_dir(), 'elites-db-');
        if ($path === false) {
            throw new RuntimeException('Unable to create temporary file for database export.');
        }

        $targetPath = $path . '.' . $extension;
        if (!@rename($path, $targetPath)) {
            @unlink($path);
            throw new RuntimeException('Unable to prepare temporary database export file.');
        }

        return $targetPath;
    }

    private function resolveMysqlSocket(array $connection): ?string
    {
        $configuredSocket = (string) data_get($connection, 'unix_socket', '');
        $candidates = [
            $configuredSocket,
            '/opt/lampp/var/mysql/mysql.sock',
            '/var/run/mysqld/mysqld.sock',
            '/var/lib/mysql/mysql.sock',
        ];

        foreach ($candidates as $candidate) {
            if ($candidate !== '' && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function buildMysqlDumpCommand(
        string $binary,
        array $connection,
        string $temporaryDumpPath,
        string $database,
        ?string $socketPath,
        ?string $hostOverride = null
    ): array {
        $command = [
            $binary,
            '--single-transaction',
            '--quick',
            '--lock-tables=false',
            '--routines',
            '--triggers',
            '--events',
            '--default-character-set=utf8mb4',
            '--user=' . (string) data_get($connection, 'username', ''),
            '--result-file=' . $temporaryDumpPath,
        ];

        if ($socketPath !== null) {
            $command[] = '--socket=' . $socketPath;
            $command[] = '--protocol=SOCKET';
        } else {
            $host = $hostOverride !== null && $hostOverride !== ''
                ? $hostOverride
                : (string) data_get($connection, 'host', '127.0.0.1');
            $command[] = '--host=' . $host;
            $command[] = '--port=' . (string) data_get($connection, 'port', '3306');
        }

        $command[] = $database;

        return $command;
    }

    private function downloadMysqlDumpViaConnection(
        string $connectionName,
        string $databaseName,
        string $downloadName,
        ?string $dumpCommandError = null
    ): Response {
        return response()->streamDownload(function () use ($connectionName, $databaseName, $dumpCommandError): void {
            $connection = DB::connection($connectionName);
            $pdo = $connection->getPdo();

            echo "-- Elites SQL dump\n";
            echo '-- Generated at: ' . now()->toDateTimeString() . "\n";
            echo '-- Database: `' . str_replace('`', '``', $databaseName) . "`\n";
            if ($dumpCommandError !== null && $dumpCommandError !== '') {
                echo '-- Note: Generated using PHP fallback because mysqldump failed: ' . str_replace(["\r", "\n"], ' ', $dumpCommandError) . "\n";
            }
            echo "\nSET FOREIGN_KEY_CHECKS=0;\n\n";

            $tables = $connection->select('SHOW FULL TABLES');
            foreach ($tables as $row) {
                $rowValues = array_values((array) $row);
                $tableName = $rowValues[0] ?? null;
                $tableType = strtoupper((string) ($rowValues[1] ?? 'BASE TABLE'));

                if (!is_string($tableName) || $tableName === '') {
                    continue;
                }

                $escapedTableName = str_replace('`', '``', $tableName);

                if ($tableType === 'VIEW') {
                    $viewRow = (array) $connection->selectOne('SHOW CREATE VIEW `' . $escapedTableName . '`');
                    $viewCreateSql = (string) ($viewRow['Create View'] ?? array_values($viewRow)[1] ?? '');
                    if ($viewCreateSql !== '') {
                        echo 'DROP VIEW IF EXISTS `' . $escapedTableName . "`;\n";
                        echo $viewCreateSql . ";\n\n";
                    }
                    continue;
                }

                $createRow = (array) $connection->selectOne('SHOW CREATE TABLE `' . $escapedTableName . '`');
                $createSql = (string) ($createRow['Create Table'] ?? array_values($createRow)[1] ?? '');
                if ($createSql === '') {
                    continue;
                }

                echo "--\n-- Table structure for `" . $escapedTableName . "`\n--\n";
                echo 'DROP TABLE IF EXISTS `' . $escapedTableName . "`;\n";
                echo $createSql . ";\n\n";
                echo "-- Data for `" . $escapedTableName . "`\n";

                $columns = null;
                $valueBatch = [];
                foreach ($connection->table($tableName)->cursor() as $record) {
                    $recordData = (array) $record;
                    if ($columns === null) {
                        $columns = array_keys($recordData);
                    }

                    $valueBatch[] = '(' . implode(', ', array_map(fn ($value): string => $this->quoteSqlValue($pdo, $value), $recordData)) . ')';
                    if (count($valueBatch) >= 200) {
                        $this->writeInsertBatch($escapedTableName, $columns, $valueBatch);
                        $valueBatch = [];
                    }
                }

                if ($columns !== null && $valueBatch !== []) {
                    $this->writeInsertBatch($escapedTableName, $columns, $valueBatch);
                }

                echo "\n";
            }

            echo "SET FOREIGN_KEY_CHECKS=1;\n";
        }, $downloadName, [
            'Content-Type' => 'application/sql; charset=UTF-8',
        ]);
    }

    private function quoteSqlValue(\PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $pdo->quote($value->format('Y-m-d H:i:s'));
        }

        return $pdo->quote((string) $value);
    }

    private function writeInsertBatch(string $tableName, array $columns, array $valueBatch): void
    {
        $escapedColumns = array_map(
            static fn (string $column): string => '`' . str_replace('`', '``', $column) . '`',
            $columns
        );

        echo 'INSERT INTO `' . $tableName . '` (' . implode(', ', $escapedColumns) . ") VALUES\n";
        echo implode(",\n", $valueBatch);
        echo ";\n";
    }
}
