<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Abbasudo\Purity\Traits\Filterable;
use Abbasudo\Purity\Traits\Sortable;

class Product extends Model
{
    use HasFactory, Filterable, Sortable;

    protected $guarded = ['created_at', 'updated_at', 'id'];

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product');
    }
}
