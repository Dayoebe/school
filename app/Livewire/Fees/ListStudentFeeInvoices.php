<?php

namespace App\Livewire\Fees;

use App\Models\User;
use Livewire\Component;

class ListStudentFeeInvoices extends Component
{
    public User $student;
    public $feeInvoices;

    public function render()
    {
        return view('livewire.fees.list-student-fee-invoices');
    }
}
