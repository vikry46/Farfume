<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Suplly extends Model
{
    use HasFactory;
    protected $table = 'supplies';
        /**
         * 
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
        'nama',
        'kode_barang',
        'total_masuk',
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

    public function produk()
    {
        return $this->hasMany(Produk::class); 
    }
    public function pengiriman(){
        return $this->hasMany(Pengiriman::class);
    }
    public function penjualan()
    {
        return $this->hasMany(Penjualan::class);
    }

}
