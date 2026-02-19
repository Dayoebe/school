@extends('layouts.app', ['mode' => 'print'])

@section('title', 'Fee Invoice - '.$feeInvoice->name)

@section('content')
    <h2>Fee Invoice</h2>
    <p><strong>Invoice:</strong> {{ $feeInvoice->name }}</p>
    <p><strong>Student:</strong> {{ $feeInvoice->user->name }}</p>
    <p><strong>Class:</strong> {{ $feeInvoice->user->studentRecord->myClass->name ?? 'N/A' }}</p>
    <p><strong>Section:</strong> {{ $feeInvoice->user->studentRecord->section->name ?? 'N/A' }}</p>
    <p><strong>Issue Date:</strong> {{ $feeInvoice->issue_date?->format('Y-m-d') }}</p>
    <p><strong>Due Date:</strong> {{ $feeInvoice->due_date?->format('Y-m-d') }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fee Item</th>
                <th>Amount</th>
                <th>Waiver</th>
                <th>Fine</th>
                <th>Paid</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($feeInvoice->feeInvoiceRecords as $record)
                @php
                    $balance = $record->amount->plus($record->fine)->minus($record->waiver)->minus($record->paid);
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $record->fee->name }}</td>
                    <td>{{ $record->amount->formatTo(app()->getLocale()) }}</td>
                    <td>{{ $record->waiver->formatTo(app()->getLocale()) }}</td>
                    <td>{{ $record->fine->formatTo(app()->getLocale()) }}</td>
                    <td>{{ $record->paid->formatTo(app()->getLocale()) }}</td>
                    <td>{{ $balance->formatTo(app()->getLocale()) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No fee records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3>Summary</h3>
    <p><strong>Total Amount:</strong> {{ $feeInvoice->amount->formatTo(app()->getLocale()) }}</p>
    <p><strong>Total Waiver:</strong> {{ $feeInvoice->waiver->formatTo(app()->getLocale()) }}</p>
    <p><strong>Total Fine:</strong> {{ $feeInvoice->fine->formatTo(app()->getLocale()) }}</p>
    <p><strong>Total Paid:</strong> {{ $feeInvoice->paid->formatTo(app()->getLocale()) }}</p>
    <p><strong>Balance:</strong> {{ $feeInvoice->balance->formatTo(app()->getLocale()) }}</p>

    @if ($feeInvoice->note)
        <h3>Note</h3>
        <p>{{ $feeInvoice->note }}</p>
    @endif
@endsection
