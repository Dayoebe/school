<?php

namespace App\Livewire\Fees;

use App\Models\FeeInvoice;
use App\Models\Fee;
use App\Models\FeeCategory;
use App\Models\FeeInvoiceRecord;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Brick\Money\Money;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class FeeInvoiceDetail extends Component
{
    use AuthorizesRequests;

    public FeeInvoice $feeInvoice;
    public $activeTab = 'details';
    
    // For adding new fees
    public $feeCategories = [];
    public $fees = [];
    public $selectedFeeCategory = '';
    public $selectedFee = '';
    public $newFeeAmount = 0;
    public $newFeeWaiver = 0;
    public $newFeeFine = 0;
    
    // For editing records
    public $editingRecordId = null;
    public $editAmount = 0;
    public $editWaiver = 0;
    public $editFine = 0;
    
    // For payments
    public $payingRecordId = null;
    public $paymentAmount = 0;

    public function mount($feeInvoiceId)
    {
        $this->feeInvoice = FeeInvoice::whereHas('user', function ($query) {
            $query->where('school_id', auth()->user()->school_id);
        })->with([
            'user.studentRecord.myClass',
            'user.studentRecord.section',
            'feeInvoiceRecords.fee'
        ])->findOrFail($feeInvoiceId);
        
        $this->loadFeeCategories();
    }

    public function loadFeeCategories()
    {
        $this->feeCategories = FeeCategory::query()->get();
        
        if ($this->feeCategories->isNotEmpty() && !$this->selectedFeeCategory) {
            $this->selectedFeeCategory = $this->feeCategories->first()->id;
            $this->updatedSelectedFeeCategory();
        }
    }

    public function updatedSelectedFeeCategory()
    {
        if ($this->selectedFeeCategory) {
            $category = $this->getFeeCategoryForCurrentSchool($this->selectedFeeCategory);
            
            // Get fees not already in this invoice
            $existingFeeIds = $this->feeInvoice->feeInvoiceRecords->pluck('fee_id')->toArray();
            $this->fees = $category ? $category->fees()->whereNotIn('id', $existingFeeIds)->get() : collect();
            
            $this->selectedFee = $this->fees->isNotEmpty() ? $this->fees->first()->id : '';
        }
    }

    public function changeTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function startEditingRecord($recordId)
    {
        $record = $this->getInvoiceRecordForCurrentInvoice($recordId);
        
        $this->editingRecordId = $recordId;
        $this->editAmount = $record->amount->getAmount()->toInt();
        $this->editWaiver = $record->waiver->getAmount()->toInt();
        $this->editFine = $record->fine->getAmount()->toInt();
    }

    public function cancelEditingRecord()
    {
        $this->editingRecordId = null;
        $this->reset(['editAmount', 'editWaiver', 'editFine']);
    }

    public function updateRecord()
    {
        $this->validate([
            'editAmount' => 'required|numeric|min:0',
            'editWaiver' => 'required|numeric|min:0',
            'editFine' => 'required|numeric|min:0',
        ]);

        $record = $this->getInvoiceRecordForCurrentInvoice($this->editingRecordId);
        
        $amount = Money::ofMinor($this->editAmount, config('app.currency'));
        $waiver = Money::ofMinor($this->editWaiver, config('app.currency'));
        $fine = Money::ofMinor($this->editFine, config('app.currency'));
        $paid = $record->paid;

        // Check if payment is higher than due
        $due = $amount->plus($fine)->minus($waiver);
        if ($due->isLessThan($paid)) {
            session()->flash('error', 'Due amount cannot be less than amount already paid');
            return;
        }

        DB::transaction(function () use ($record) {
            $record->update([
                'amount' => $this->editAmount,
                'waiver' => $this->editWaiver,
                'fine' => $this->editFine,
            ]);
        });

        $this->feeInvoice->refresh();
        $this->cancelEditingRecord();
        session()->flash('success', 'Fee record updated successfully');
    }

    public function addNewFee()
    {
        $this->validate([
            'selectedFee' => 'required|exists:fees,id',
            'newFeeAmount' => 'required|numeric|min:0',
            'newFeeWaiver' => 'nullable|numeric|min:0',
            'newFeeFine' => 'nullable|numeric|min:0',
        ]);

        $fee = $this->getFeeForCurrentSchool($this->selectedFee);
        if (!$fee) {
            session()->flash('error', 'Selected fee is not in your current school.');
            return;
        }

        $alreadyExists = $this->feeInvoice->feeInvoiceRecords()
            ->where('fee_id', $fee->id)
            ->exists();
        if ($alreadyExists) {
            session()->flash('error', 'Selected fee is already attached to this invoice.');
            return;
        }

        DB::transaction(function () {
            $this->feeInvoice->feeInvoiceRecords()->create([
                'fee_id' => $this->selectedFee,
                'amount' => $this->newFeeAmount,
                'waiver' => $this->newFeeWaiver ?? 0,
                'fine' => $this->newFeeFine ?? 0,
                'paid' => 0,
            ]);
        });

        $this->feeInvoice->refresh();
        $this->reset(['newFeeAmount', 'newFeeWaiver', 'newFeeFine']);
        $this->updatedSelectedFeeCategory();
        
        session()->flash('success', 'Fee added to invoice successfully');
    }

    public function deleteRecord($recordId)
    {
        $record = $this->getInvoiceRecordForCurrentInvoice($recordId);
        
        DB::transaction(function () use ($record) {
            $record->delete();
        });

        $this->feeInvoice->refresh();
        session()->flash('success', 'Fee record deleted successfully');
    }

    public function startPayment($recordId)
    {
        $this->payingRecordId = $recordId;
        $this->paymentAmount = 0;
    }

    public function cancelPayment()
    {
        $this->payingRecordId = null;
        $this->paymentAmount = 0;
    }

    public function addPayment()
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
        ]);

        $record = $this->getInvoiceRecordForCurrentInvoice($this->payingRecordId);
        
        $pay = Money::of($this->paymentAmount, config('app.currency'));
        $paid = $record->paid;
        $newAmount = $paid->plus($pay);

        // Check if payment is higher than due
        $due = $record->amount->plus($record->fine)->minus($record->waiver);
        if ($newAmount->isGreaterThan($due)) {
            session()->flash('error', 'Payment cannot be higher than the total amount due');
            return;
        }

        DB::transaction(function () use ($record, $newAmount) {
            $record->update([
                'paid' => $newAmount,
            ]);
        });

        $this->feeInvoice->refresh();
        $this->cancelPayment();
        session()->flash('success', 'Payment added successfully');
    }

    protected function getFeeCategoryForCurrentSchool($feeCategoryId): ?FeeCategory
    {
        if (!$feeCategoryId) {
            return null;
        }

        return FeeCategory::query()
            ->find($feeCategoryId);
    }

    protected function getFeeForCurrentSchool($feeId): ?Fee
    {
        if (!$feeId) {
            return null;
        }

        return Fee::whereHas('feeCategory', function ($query) {
            $query->where('school_id', auth()->user()->school_id);
        })->find($feeId);
    }

    protected function getInvoiceRecordForCurrentInvoice($recordId): FeeInvoiceRecord
    {
        return FeeInvoiceRecord::where('fee_invoice_id', $this->feeInvoice->id)
            ->whereHas('feeInvoice.user', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->findOrFail($recordId);
    }

    public function render()
    {
        return view('livewire.fees.fee-invoice-detail', [
            'feeInvoice' => $this->feeInvoice,
        ])
        ->layout('layouts.dashboard', [
            'breadcrumbs' => [
                ['href' => route('dashboard'), 'text' => 'Dashboard'],
                ['href' => route('fee-invoices.index'), 'text' => 'Fee Invoices'],
                ['href' => route('fee-invoices.show', $this->feeInvoice->id), 'text' => $this->feeInvoice->name, 'active' => true],
            ]
        ])
        ->title($this->feeInvoice->name);
    }
}
