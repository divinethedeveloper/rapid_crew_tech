<?php

namespace App\Models;

use Abbasudo\Purity\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeProduct extends Model
{
    use HasFactory;
    use Filterable;
    
    protected $guarded = ['created_at', 'updated_at', 'id'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
