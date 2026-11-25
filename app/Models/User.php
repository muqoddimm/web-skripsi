<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'address', 'role', 'is_ai_enabled', 'is_chat_deleted'
    ];

    protected $casts = [
        'is_ai_enabled'   => 'boolean',
        'is_chat_deleted' => 'boolean',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    // Relasi ke PreOrder (1 User bisa memiliki banyak Pre-Order)
    public function preOrders(): HasMany
    {
        return $this->hasMany(PreOrder::class);
    }

    // Relasi ke Cart
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    // Cek apakah pengguna adalah admin (Sesuai Class Diagram)
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}