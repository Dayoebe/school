<?php

namespace App\Http\Controllers;

use App\Models\FeeInvoice;
use Barryvdh\DomPDF\Facade\Pdf;

class FeeInvoiceController extends Controller
{
    public function print(FeeInvoice $fee_invoice)
    {
        $feeInvoice = $fee_invoice->load([
            'user.studentRecord.myClass',
            'user.studentRecord.section',
            'feeInvoiceRecords.fee',
        ]);

        if ($feeInvoice->user->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        $pdf = Pdf::loadView('pages.fee-invoice.print', [
            'feeInvoice' => $feeInvoice,
        ]);

        $pdf->getDomPDF()->setHttpContext(
            stream_context_create([
                'ssl' => [
                    'allow_self_signed' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ])
        );

        return $pdf->stream("fee-invoice-{$feeInvoice->id}.pdf");
    }
}
