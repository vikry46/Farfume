<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pengiriman extends Model
{
    use HasFactory;
    protected $table = 'pengiriman';
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
        'id_supplie',
        'jumlah_kirim',
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
    public function market()
    {
        return $this->belongsTo(Market::class, 'id_market', 'id');
    }
    
    public function supplie()
    {
        return $this->belongsTo(Suplly::class, 'id_supplie', 'id');
    }
}
