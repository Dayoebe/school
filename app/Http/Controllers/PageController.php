<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home() {
        return view('livewire.site.home');
    }

    public function about() {
        return view('livewire.site.about');
    }

    public function contact() {
        return view('livewire.site.contact');
    }

    public function admission() {
        return view('livewire.site.admission');
    }

    public function gallery() {
        return view('livewire.site.gallery');
    }

    // Add more as needed...
}

