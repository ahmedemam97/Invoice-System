<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Section;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $sections = Section::all();
        $products = Product::all();
        return view('products.products', compact('sections','products'));
    }

    //================================================================

    public function create()
    {
        //
    }

    //================================================================

    public function store(Request $request)
    {
        Product::create(
            [
                'product_name'=>$request->product_name,
                'section_id' => $request->section_id,
                'description'=>$request->description
            ]);

        session()->flash('Add','تم اضافة المنتج بنجاح');
        return redirect('/products');
    }

    //================================================================
    public function show(Request $request)
    {

    }

    //================================================================

    public function edit(Product $product)
    {
        //
    }

    //================================================================

    public function update(Request $request)
    {
        $id = Section::where('section_name', $request->section_name)->first()->id;
        
        
        $products = Product::findOrFail($request->pro_id);
        
        $products->update([
            'product_name'=>$request->product_name,
            'desctiption'=> $request->description,
            'product_id' => $id
        ]);

        session()->flash('edit', 'تم تعديل المنتج بنجاح');
        return back();
    }

    //================================================================
    public function destroy(Request $request)
    {
        $products = Product::findOrFail($request->pro_id);
        $products->delete();
        session()->flash('delete', 'تم حذف المنتج بنجاح');
        return back();    }
}
