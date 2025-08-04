<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualans';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'id_supplie',
        'id_market',
        'terjual',
        'estimasi_botol',
        'ukuran_botol',
        'harga',
        'tanggal',
        'delete',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($penjualan) {
            $penjualan->id = (string) Str::uuid();
        });
    }

    public function market()
    {
        return $this->belongsTo(Market::class, 'id_market');
    }

    public function supplie()
    {
        return $this->belongsTo(Suplly::class, 'id_supplie');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
