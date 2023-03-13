<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Section;
use App\Models\Invoices;
use Illuminate\Http\Request;
use App\Exports\InvoicesExport;
use App\Models\InvoicesDetails;
use App\Notifications\AddInvoice;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice_attachments;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Notifications\Add_invoice_new;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;


class InvoicesController extends Controller
{

    public function index()
    {
        $invoices = Invoices::all();
        return view('invoices.invoices',compact('invoices'));
    }

    //=====================================================

    public function create()
    {
        $sections = Section::all();
        return view('invoices.add_invoice',compact('sections'));
    }

    //=====================================================

    public function store(Request $request)
    {
        invoices::create([
            'invoice_number' => $request->invoice_number,
            'invoice_Date' => $request->invoice_Date,
            'Due_date' => $request->Due_date,
            'product' => $request->product,
            'section_id' => $request->Section,
            'Amount_collection' => $request->Amount_collection,
            'Amount_Commission' => $request->Amount_Commission,
            'Discount' => $request->Discount,
            'Value_VAT' => $request->Value_VAT,
            'Rate_VAT' => $request->Rate_VAT,
            'Total' => $request->Total,
            'Status' => 'غير مدفوعة',
            'Value_Status' => 2,
            'note' => $request->note,
        ]);

        $invoice_id = Invoices::latest()->first()->id;
        InvoicesDetails::create([
            'id_Invoice' => $invoice_id,
            'invoice_number' => $request->invoice_number,
            'product' => $request->product,
            'Section' => $request->Section,
            'Status' => 'غير مدفوعة',
            'Value_Status' => 2,
            'note' => $request->note,
            'user' => (Auth::user()->name ?? 'Name'),
        ]);


        if ($request->hasFile('pic')) {

            $invoice_id = Invoices::latest()->first()->id;
            $image = $request->file('pic');
            $file_name = $image->getClientOriginalName();
            $invoice_number = $request->invoice_number;

            $attachments = new Invoice_attachments();
            $attachments->file_name = $file_name;
            $attachments->invoice_number = $invoice_number;
            $attachments->Created_by = Auth::user()->name ?? 'Name';
            $attachments->invoice_id = $invoice_id;
            $attachments->save();

            // move pic
            $imageName = $request->pic->getClientOriginalName();
            $request->pic->move(public_path('Attachments/' . $invoice_number), $imageName);
        }
        // // Notification
        // $user = User::first();
        // //end Notification
        
        
        $user = User::find(Auth::user()->id);
        $invoices = Invoices::latest()->first();
        Notification::send($user, new Add_invoice_new($invoices));
        
        

        session()->flash('Add', 'تم اضافة الفاتورة بنجاح');
        return back();
    }

    //=====================================================

    public function show($id)
    {
        $invoices = Invoices::where('id', $id)->first();
        return view('invoices.status_update', compact('invoices')); 
    }

    //=====================================================

    public function edit($id)
    {
        $invoices = Invoices::where('id', $id)->first();
        $sections = Section::all();

        return view('invoices.edit_invoice', compact(['invoices', 'sections']));
    }

    //==========================================================

    public function update(Request $request)
    {
        $invoices = Invoices::findOrFail($request->invoice_id);

        $invoices->update([
            'invoice_number' => $request->invoice_number,
            'invoice_Date' => $request->invoice_Date,
            'Due_date' => $request->Due_date,
            'product' => $request->product,
            'section_id' => $request->Section,
            'Amount_collection' => $request->Amount_collection,
            'Amount_Commission' => $request->Amount_Commission,
            'Discount' => $request->Discount,
            'Value_VAT' => $request->Value_VAT,
            'Rate_VAT' => $request->Rate_VAT,
            'Total' => $request->Total,
            'note' => $request->note,
        ]);

        session()->flash('edit', 'تم تعديل الفاتورة بنجاح');
        return back();
    }

    //==========================================================

    public function destroy(Request $request)
    {
        $id = $request->invoice_id;
        $invoices = Invoices::where('id', $id)->first();
        $attachments = Invoice_attachments::where('invoice_id', $id)->first();

        $id_page =$request->id_page;
        
        if (!$id_page==2) {
            if (!empty($attachments->invoice_number)) {

                Storage::disk('public_uploads')->deleteDirectory($attachments->invoice_number);
            }
            if($invoices != null)
            {
            $invoices->forceDelete();
            session()->flash('delete_invoice');
            return redirect('/invoices');
            }

            return redirect('/invoices');

        } 
        else 
        {
            if($invoices != null){
                $invoices->delete();
                session()->flash('archive_invoice');
                return redirect('/Archive');
            }
            
            session()->flash('archive_invoice');
            return redirect('/Archive');
        }
    }

//--------------------------------------------------------------------------------    

    public function getproducts($id)
    {
        $products = DB::table("products")->where("section_id", $id)->pluck("Product_name", "id");
        return json_encode($products);
    }

//--------------------------------------------------------------------------------

    public function Status_Update($id, Request $request)
    {
        $invoices = Invoices::findOrFail($id);

        if ($request->Status === 'مدفوعة') {

            $invoices->update([
                'Value_Status' => 1,
                'Status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);

            InvoicesDetails::create([
                'id_Invoice' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => $request->Status,
                'Value_Status' => 1,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => (Auth::user()->name ?? 'Name'),
            ]);
        }

        else {
            $invoices->update([
                'Value_Status' => 3,
                'Status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);
            InvoicesDetails::create([
                'id_Invoice' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => $request->Status,
                'Value_Status' => 3,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => (Auth::user()->name ?? 'Name'),
            ]);
        }
        session()->flash('Status_update');
        return redirect('/invoices');
    }

//--------------------------------------------------------------------------------

    public function invoices_paid()
    {
        $invoices = Invoices::where('Value_Status', 1)->get();
        return view('invoices.invoices_paid',compact('invoices'));
    }

//--------------------------------------------------------------------------------

    public function invoices_unpaid()
    {
        $invoices = Invoices::where('Value_Status',2)->get();
        return view('invoices.invoices_unpaid',compact('invoices'));
    }

//--------------------------------------------------------------------------------

    public function invoices_partial()
    {
        $invoices = Invoices::where('Value_Status',3)->get();
        return view('invoices.invoices_partial',compact('invoices'));
    }

//--------------------------------------------------------------------------------

    public function print_invoice($id)
    {
        $invoices = Invoices::where('id', $id)->first();
        return view('invoices.print_invoice', compact('invoices'));
    }

//--------------------------------------------------------------------------------

    public function export() 
    {
        return Excel::download(new InvoicesExport, 'invoices.xlsx');
    }

// --------------------------------------------------------------------------------

public function MarkAsRead_all (Request $request)
{
    
    $userUnreadNotification= auth()->user()->unreadNotifications;
    
    if($userUnreadNotification) {
        $userUnreadNotification->markAsRead();
        return back();
    }
}

// --------------------------------------------------------------------------------

public function unreadNotifications_count()

{
    return auth()->user()->unreadNotifications->count();
}

// --------------------------------------------------------------------------------

public function unreadNotifications()

{
    foreach (auth()->user()->unreadNotifications as $notification){

    return $notification->data['title'];

    }
}
}
