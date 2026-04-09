<?php

namespace App\Models;

use Abbasudo\Purity\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    use Filterable;
    protected $guarded = ['id, created_at, updated_at'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products', 'order', 'product')
                    ->withPivot('quantity as order_quantity')
                    ->withPivot('price as order_price')
                    ->withPivot('status as order_status')
                    ->withPivot('id as order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}
