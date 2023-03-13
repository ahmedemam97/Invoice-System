<?php

use App\Models\Invoices;
use App\Models\InvoicesDetails;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Invoices_Report;
use App\Http\Controllers\Customers_Report;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\InvoiceArchiveController;
use App\Http\Controllers\InvoicesDetailsController;
use App\Http\Controllers\InvoiceAttachmentsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
        
Auth::routes();
    
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),     //we have write all of routes in jetstream route to add security in the website
    'verified'                            //without it , anyone can enter the website without login
])->group(function () {
    Route::post('update', [ProductController::class,'update']);
    Route::post('destroy', [ProductController::class,'destroy']);

    Route::get('/', function () {
        return view('auth.login');
    });

    // Auth::routes(['register' => false]);  // to cancellation the register

    Route::get('/index', function(){


        $count_all =invoices::count();
        $count_invoices1 = invoices::where('Value_Status', 1)->count();
        $count_invoices2 = invoices::where('Value_Status', 2)->count();
        $count_invoices3 = invoices::where('Value_Status', 3)->count();
        
        if($count_invoices2 == 0){
            $nspainvoices2=0;
        }
        else{
            $nspainvoices2 = $count_invoices2/ $count_all*100;
        }
        
        if($count_invoices1 == 0){
            $nspainvoices1=0;
        }
        else{
            $nspainvoices1 = $count_invoices1/ $count_all*100;
        }
        
        if($count_invoices3 == 0){
            $nspainvoices3=0;
        }
        else{
            $nspainvoices3 = $count_invoices3/ $count_all*100;
        }
        $chartjs = app()->chartjs
                    ->name('barChartTest')
                    ->type('bar')
                    ->size(['width' => 350, 'height' => 200])
                    ->labels(['الفواتير الغير المدفوعة', 'الفواتير المدفوعة','الفواتير المدفوعة جزئيا'])
                    ->datasets([
                        [
                            "label" => "الفواتير الغير المدفوعة",
                            'backgroundColor' => ['#ec5858'],
                            'data' => [$nspainvoices2]
                        ],
                        [
                            "label" => "الفواتير المدفوعة",
                            'backgroundColor' => ['#81b214'],
                            'data' => [$nspainvoices1]
                        ],
                        [
                            "label" => "الفواتير المدفوعة جزئيا",
                            'backgroundColor' => ['#ff9642'],
                            'data' => [$nspainvoices3]
                        ],
        
        
                    ])
                    ->options([]);
        
                //احصائية نسبة تنفيذ الحالات 
        
                $chartjs_2 = app()->chartjs
                ->name('pieChartTest')
                ->type('pie')
                ->size(['width' => 340, 'height' => 280])
                ->labels(['الفواتير الغير المدفوعة', 'الفواتير المدفوعة','الفواتير المدفوعة جزئيا'])
                ->datasets([
                    [
                        "label" => "نسبة الفواتير",
                        'backgroundColor' => "#3087E5",
                        'borderColor' => "rgba(38, 185, 154, 0.7)",
                        "pointBorderColor" => "rgba(38, 185, 154, 0.7)",
                        "pointBackgroundColor" => "rgba(38, 185, 154, 0.7)",
                        "pointHoverBackgroundColor" => "#fff",
                        "pointHoverBorderColor" => "rgba(220,220,220,1)",
                        'data' => [65, 59, 80, 81, 56, 55, 40],
                        'data' => [$nspainvoices2, $nspainvoices1,$nspainvoices3]
                    ]
                ])
                ->options([]);
                $list = [$chartjs, $chartjs_2];
        
        return view('index', compact('list'));
        

    });

                


    Route::resource('invoices', InvoicesController::class);

    Route::resource('sections', SectionController::class);

    Route::resource('products', ProductController::class);

    Route::get('/section/{id}',[InvoicesController::class, 'getproducts']);

    Route::get('/InvoicesDetails/{id}',[InvoicesDetailsController::class,'edit']);

    Route::get('/View_file/{invoice_number}/{file_name}',[InvoicesDetailsController::class,'open_file']);

    Route::get('download/{invoice_number}/{file_name}', [InvoicesDetailsController::class,'get_file']);

    Route::post('delete_file', [InvoicesDetailsController::class],'destroy')->name('delete_file');

    Route::resource('InvoiceAttachments', '\App\Http\Controllers\InvoiceAttachmentsController');

    Route::post('/Status_Update/{id}', [InvoicesController::class,'Status_Update'])->name('Status_Update');

    Route::get('/Status_show/{id}', [InvoicesController::class,'show'])->name('Status_show');

    Route::get('/edit_invoice/{id}', [InvoicesController::class,'edit']);

    Route::get('invoices_paid', [InvoicesController::class, 'invoices_paid']);

    Route::get('invoices_partial', [InvoicesController::class, 'invoices_partial']);

    Route::get('invoices_unpaid', [InvoicesController::class, 'invoices_unpaid']);

    Route::resource('Archive', InvoiceArchiveController::class);

    Route::get('print_invoice/{id}',[InvoicesController::class,'print_invoice']);

    Route::get('export_invoices', [InvoicesController::class, 'export']);

    Route::get('invoices_report', [Invoices_Report::class,'index']);

    Route::post('Search_invoices', [Invoices_Report::class,'Search_invoices']);

    Route::get('customers_report', [Customers_Report::class, 'index']);

    Route::post('Search_customers', [Customers_Report::class,'Search_customers']);

    Route::get('MarkAsRead_all', [InvoicesController::class, 'MarkAsRead_all'])->name('MarkAsRead_all');

    Route::group(['middleware' => ['auth']], function() {
        
        Route::resource('roles',RoleController::class);
        Route::resource('users',UserController::class);
    });
        Route::get('/dashboard', function () { 

        return view('index');       // the name was changed from RouteServiceProvider from dashboard to index
        });
});
