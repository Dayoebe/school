<?php

namespace App\Support;

class PermissionMatrix
{
    /**
     * Role => permission list.
     * Use "*" to grant all known permissions.
     */
    public static function roles(): array
    {
        $principal = [
            'view dashboard',
            'manage own profile',
            'change own password',

            'read school',
            'update school',
            'manage school settings',

            'read teacher',
            'create teacher',
            'update teacher',
            'delete teacher',

            'read student',
            'create student',
            'update student',
            'delete student',
            'promote student',
            'read promotion',
            'reset promotion',
            'graduate student',
            'view graduations',
            'reset graduation',

            'read parent',
            'create parent',
            'update parent',
            'delete parent',

            'read academic year',
            'create academic year',
            'update academic year',
            'delete academic year',
            'set academic year',
            'read semester',
            'create semester',
            'update semester',
            'delete semester',
            'set semester',
            'read class group',
            'create class group',
            'update class group',
            'delete class group',
            'read class',
            'create class',
            'update class',
            'delete class',
            'read section',
            'create section',
            'update section',
            'delete section',
            'read subject',
            'create subject',
            'update subject',
            'delete subject',
            'read syllabus',
            'create syllabus',
            'update syllabus',
            'delete syllabus',

            'read exam',
            'create exam',
            'update exam',
            'delete exam',
            'read exam slot',
            'create exam slot',
            'update exam slot',
            'delete exam slot',
            'read exam record',
            'create exam record',
            'check result',
            'read grade system',
            'create grade system',
            'update grade system',
            'delete grade system',
            'upload result',
            'view result',

            'read timetable',
            'create timetable',
            'update timetable',
            'delete timetable',
            'read custom timetable item',
            'create custom timetable item',
            'update custom timetable item',
            'delete custom timetable item',

            'read notice',
            'create notice',
            'update notice',
            'delete notice',

            'read fee category',
            'create fee category',
            'update fee category',
            'delete fee category',
            'read fee',
            'create fee',
            'update fee',
            'delete fee',
            'read fee invoice',
            'create fee invoice',
            'update fee invoice',
            'delete fee invoice',

            'read admission registration',
            'manage admission registration',
            'read contact message',
            'reply contact message',
            'manage gallery',

            'take cbt exam',
            'view cbt result',
            'manage cbt',

            'read applicant',
            'update applicant',
        ];

        $teacher = [
            'view dashboard',
            'manage own profile',
            'change own password',

            'read student',
            'read parent',

            'read academic year',
            'read semester',
            'read class group',
            'read class',
            'read section',
            'read subject',
            'update subject',
            'read syllabus',

            'read exam',
            'read exam slot',
            'read exam record',
            'create exam record',
            'check result',
            'upload result',
            'view result',

            'read timetable',
            'read custom timetable item',

            'read notice',
            'create notice',
            'update notice',

            'read admission registration',
            'manage admission registration',
            'read contact message',
            'reply contact message',
            'manage gallery',

            'take cbt exam',
            'view cbt result',
            'manage cbt',
        ];

        $student = [
            'view dashboard',
            'manage own profile',
            'change own password',
            'view result',
            'take cbt exam',
            'view cbt result',
        ];

        $parent = [
            'view dashboard',
            'manage own profile',
            'change own password',
            'view result',
            'view cbt result',
        ];

        $basicUser = [
            'view dashboard',
            'manage own profile',
            'change own password',
        ];

        return [
            'super-admin' => ['*'],
            'super_admin' => ['*'],
            'principal' => $principal,
            // Keep admin role for backward compatibility with existing users.
            'admin' => array_values(array_unique(array_merge($principal, [
                'read admin',
                'create admin',
                'update admin',
                'delete admin',
                'lock user',
            ]))),
            'teacher' => $teacher,
            'student' => $student,
            'parent' => $parent,
            'user' => $basicUser,
        ];
    }

    /**
     * Full application permission catalog.
     */
    public static function permissions(): array
    {
        return [
            'view dashboard',
            'manage own profile',
            'change own password',

            'read school',
            'create school',
            'update school',
            'delete school',
            'manage school settings',

            'read admin',
            'create admin',
            'update admin',
            'delete admin',
            'lock user',
            'read applicant',
            'update applicant',

            'read teacher',
            'create teacher',
            'update teacher',
            'delete teacher',

            'read student',
            'create student',
            'update student',
            'delete student',
            'promote student',
            'read promotion',
            'reset promotion',
            'graduate student',
            'view graduations',
            'reset graduation',

            'read parent',
            'create parent',
            'update parent',
            'delete parent',

            'read academic year',
            'create academic year',
            'update academic year',
            'delete academic year',
            'set academic year',
            'read semester',
            'create semester',
            'update semester',
            'delete semester',
            'set semester',
            'read class group',
            'create class group',
            'update class group',
            'delete class group',
            'read class',
            'create class',
            'update class',
            'delete class',
            'read section',
            'create section',
            'update section',
            'delete section',
            'read subject',
            'create subject',
            'update subject',
            'delete subject',
            'read syllabus',
            'create syllabus',
            'update syllabus',
            'delete syllabus',

            'read exam',
            'create exam',
            'update exam',
            'delete exam',
            'read exam slot',
            'create exam slot',
            'update exam slot',
            'delete exam slot',
            'read exam record',
            'create exam record',
            'check result',
            'read grade system',
            'create grade system',
            'update grade system',
            'delete grade system',
            'upload result',
            'view result',

            'read timetable',
            'create timetable',
            'update timetable',
            'delete timetable',
            'read custom timetable item',
            'create custom timetable item',
            'update custom timetable item',
            'delete custom timetable item',

            'read notice',
            'create notice',
            'update notice',
            'delete notice',

            'read fee category',
            'create fee category',
            'update fee category',
            'delete fee category',
            'read fee',
            'create fee',
            'update fee',
            'delete fee',
            'read fee invoice',
            'create fee invoice',
            'update fee invoice',
            'delete fee invoice',

            'read admission registration',
            'manage admission registration',
            'read contact message',
            'reply contact message',
            'manage gallery',

            'take cbt exam',
            'view cbt result',
            'manage cbt',
        ];
    }

    public static function allPermissionsForRole(string $role): array
    {
        $roles = self::roles();
        $permissions = $roles[$role] ?? [];

        if ($permissions === ['*']) {
            return self::permissions();
        }

        return $permissions;
    }
}
