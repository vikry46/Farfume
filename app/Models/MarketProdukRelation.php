<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class MarketProdukRelation extends Model
{
    
    use HasFactory;
    protected $table='market_produk';
        /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'id_market',
        'id_produk',
        'delete'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            $product->id = (string) Str::uuid();
        });
    }

    public function market()
    {
        return $this->belongsTo(Market::class,'id_market');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class,'id_produk');
    }
}

