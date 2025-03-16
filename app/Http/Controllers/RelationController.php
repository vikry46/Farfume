<?php

namespace App\Http\Controllers;

use App\Models\MarketProdukRelation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelationController extends Controller
{
    public function store(Request $request)
    {

        $zeeChristyVikry=MarketProdukRelation::create([
            'id_market' => $request->id_market,
            'id_produk' => $request->id_produk,
   
        ]);
        return response()->json([
            'data'=>$zeeChristyVikry,
            'message'=>' data berhasil di simpan'
        ],201);
    }

    public function index(){
        $christy=MarketProdukRelation::with(['market','produk'])->get();
        return response()->json([
            'data'=>$christy
        ],201);
    }
}
