<?php

namespace App\Http\Controllers;

use App\Models\FeeInvoice;
use App\Traits\ResolvesAccessibleStudents;
use Barryvdh\DomPDF\Facade\Pdf;

class FeeInvoiceController extends Controller
{
    use ResolvesAccessibleStudents;

    public function print(FeeInvoice $fee_invoice)
    {
        $query = FeeInvoice::query()
            ->whereKey($fee_invoice->getKey())
            ->whereHas('user', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            });

        if ($this->isRestrictedStudentPortalViewer()) {
            $studentUserIds = $this->portalAccessibleStudentUserIds();

            if ($studentUserIds->isEmpty()) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('user_id', $studentUserIds);
            }
        }

        $feeInvoice = $query->with([
            'user.studentRecord.myClass',
            'user.studentRecord.section',
            'feeInvoiceRecords.fee',
        ])->firstOrFail();

        $pdf = Pdf::loadView('livewire.fees.pages.invoice-print', [
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
