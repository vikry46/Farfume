<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Market extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'nama',
        'kode_cabang',
        'delete',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($market) {
            $market->id = (string) Str::uuid();
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_markets', 'market_id', 'user_id')
                    ->withPivot('market_role', 'is_active')
                    ->wherePivot('is_active', true)
                    ->withTimestamps();
    }

    public function marketProduk()
    {
        return $this->hasMany(MarketProdukRelation::class);
    }

    public function marketPengiriman()
    {
        return $this->hasMany(Pengiriman::class);
    }

    public function marketPenjualan()
    {
        return $this->hasMany(Penjualan::class);
    }
}
