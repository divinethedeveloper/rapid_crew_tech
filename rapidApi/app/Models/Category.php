<?php

namespace App\Models;

use Abbasudo\Purity\Traits\Filterable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, Filterable;

    protected $guarded = ['id', 'created_at', 'updated_at'];
}
