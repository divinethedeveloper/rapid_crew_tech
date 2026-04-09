<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;

    protected $guarded = ['id, created_at, updated_at'];

    public function order()
    {
        return $this->hasOne(Order::class, 'order');
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'product');
    }
}
