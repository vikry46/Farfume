<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $appends = ['role'];

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getRoleAttribute()
    {
        return $this->getRoleNames()->first();
    }

    public function markets()
    {
        return $this->belongsToMany(Market::class, 'user_markets', 'user_id', 'market_id')
                    ->withPivot('market_role', 'is_active')
                    ->wherePivot('is_active', true)
                    ->withTimestamps();
    }

    public function hasAccessToMarket($marketId)
    {
        if ($this->hasRole('superadmin')) return true;

        return $this->markets()->where('market_id', $marketId)->exists();
    }

    public function getRoleInMarket($marketId)
    {
        if ($this->hasRole('superadmin')) return 'superadmin';

        $market = $this->markets()->where('market_id', $marketId)->first();
        return $market ? $market->pivot->market_role : null;
    }
}
