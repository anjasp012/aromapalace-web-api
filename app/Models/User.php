<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'role',
        'phone',
        'avatar',
        'birthdate',
        'gender',
        'password',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birthdate' => 'date',
            'password' => 'hashed',
        ];
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function primaryAddress(): HasOne
    {
        return $this->hasOne(UserAddress::class)->where('is_primary', true);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function membership(): HasOne
    {
        return $this->hasOne(Membership::class);
    }

    public function pointHistories(): HasMany
    {
        return $this->hasMany(PointHistory::class)->orderBy('created_at', 'desc');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }

    public function searchHistories(): HasMany
    {
        return $this->hasMany(UserSearchHistory::class)->orderBy('created_at', 'desc');
    }
}
