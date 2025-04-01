<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Penjualan extends Model
{
    use HasFactory;
    protected $table='penjualans';
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
    'id_supplie',
    'id_market',
    'terjual',
    'tanggal',
    'delete'
];

protected static function boot()
{
    parent::boot();

    static::creating(function ($product) {
        $product->id = (string) Str::uuid();
    });
}
public function Market()
{
    return $this->belongsTo(Market::class,'id_market');
}
public function Supplie()
{
    return $this->belongsTo(Suplly::class,'id_supplie');
}
}
