<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProdukController extends Controller
{
    public function index(){
        $product=Produk::with('supply')->get();
        return response()->json([
            'data'=>$product
        ],201);
    }
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'id_supply'=> 'required|string|max:255',

        ],[
            'id_supply.required'=>'Id supply wajib di isi !!!',
            'id_supply.integer'=>'id supply berupa anga bukan huruf !!!',
 
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product = Produk::create([
            'id_supply' => $request->id_supply,

        ]);

        return response()->json([
            'data'=>$product,
            'message'=>'id_supply berhasil di simpan'
        ],201);
    }
    public function show($id){
        $zee = Produk::where('id',$id)->first();
        return response()->json([
            'data'=>$zee,
            'message'=>'Id supply sudah ditemukan',
        ],201);
    }
    public function update(Request $request,Produk $id)
    {
        $validator = Validator::make($request->all(), [
            'id_supply' => 'required|string|max:255',
        ],[
            'id_supply.required'=>'kode barang waji di isi !!!',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $id->update($request->all());

        return response()->json($id);
    }
    public function delete(Produk $id)
    {
        $id->delete();
        return response()->json(['message' => 'Data Berhasil di hapus']);
    }
}
