<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Invoices;
use Illuminate\Http\Request;
use App\Models\InvoicesDetails;
use App\Models\Invoice_attachments;
use Illuminate\Support\Facades\Storage;

class InvoicesDetailsController extends Controller
{
    
    public function index()
    {
        //
    }

//---------------------------------------------------------------------------------------------------    
    
    public function create()
    {
        //
    }

//---------------------------------------------------------------------------------------------------
    
    public function store(Request $request)
    {
        //
    }

//---------------------------------------------------------------------------------------------------
    
    public function show(InvoicesDetails $invoicesDetails)
    {
        //
    }

//---------------------------------------------------------------------------------------------------
    
    public function edit($id)
    {
        $invoices = Invoices::where('id', $id)->first();
        $details = InvoicesDetails::where('id_Invoice', $id)->get();
        $attachments = Invoice_attachments::where('invoice_id', $id)->get();
         
        return view('invoices.details_invoice', compact(['invoices', 'details', 'attachments']));
    }

//---------------------------------------------------------------------------------------------------
    
    public function update(Request $request, InvoicesDetails $invoicesDetails)
    {
        //
    }

//---------------------------------------------------------------------------------------------------    

public function destroy(Request $request)
{
    $invoices = Invoice_Attachments::findOrFail($request->id_file);
    $invoices->delete();
    Storage::disk('public_uploads')->delete($request->invoice_number.'/'.$request->file_name);
    session()->flash('delete', 'تم حذف المرفق بنجاح');
    return back();
}

//---------------------------------------------------------------------------------------------------

    public function open_file($invoice_number, $file_name)
    {
        $files = Storage::disk('public_uploads')->getDriver()
        ->getAdapter()->applyPathPrefix($invoice_number.'/'.$file_name);
        
        return response()->file($files);
    }

//---------------------------------------------------------------------------------------------------

    public function get_file($invoice_number,$file_name)

    {
        $contents= Storage::disk('public_uploads')->getDriver()->getAdapter()->applyPathPrefix($invoice_number.'/'.$file_name);
        return response()->download( $contents);
    }
}
