<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected $fillable = [
        'category_id',
        'title',
        'description',
        'image',
        'price',
        'stock',
    ];

    use HasFactory;
    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function orders() {
        return $this->belongsToMany(Order::class, 'order_product');
    }

    public function carts() {
        return $this->belongsToMany(Cart::class, 'cart_product')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
