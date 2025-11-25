<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded=[''];
    public function orders(){
        return $this->belongsToMany(Order::class)->withPivot('quantity')->withTimestamps();

    }
    public function category(){
        return $this->belongsTo(Category::class);
    }
    public function categories(){
        return $this->belongsToMany(Category::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
     
}
