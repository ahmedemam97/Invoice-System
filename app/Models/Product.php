<?php

namespace App\Models;

use App\Models\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['product_name','description','section_id'];

    //protected $guarded = [];  //  ==>> other way to whrite all columns
    
       
    public function section()
    {
        return $this->belongsTo(Section::class);
    }
    
}
