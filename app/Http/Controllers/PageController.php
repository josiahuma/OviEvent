<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function how()
    {
        return view('pages.how');
    }

    public function pricing()
    {
        return view('pages.pricing');
    }
}
