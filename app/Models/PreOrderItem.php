<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreOrderItem extends Model
{
    protected $guarded = ['id'];

    public function preOrder(): BelongsTo
    {
        return $this->belongsTo(PreOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}