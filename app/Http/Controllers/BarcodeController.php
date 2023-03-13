<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function barcode()
	{
	    return view('barcode');
	}
}