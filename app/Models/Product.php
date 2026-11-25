<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $guarded = ['id'];

    // Relasi ke PreOrderItem
    public function preOrderItems(): HasMany
    {
        return $this->hasMany(PreOrderItem::class);
    }

    // Relasi ke Cart
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }
}