<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home() {
        return view('pages.home');
    }

    public function about() {
        return view('pages.about');
    }

    public function contact() {
        return view('pages.contact');
    }

    public function admission() {
        return view('pages.admission');
    }

    public function gallery() {
        return view('pages.gallery');
    }

    // Add more as needed...
}

