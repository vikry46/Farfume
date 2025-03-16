<?php

namespace App\Http\Controllers;

use App\Models\Suplly;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupllyController extends Controller
{
    // pemanggilan data berdasarkan tanggal
    
    // public function index(Request $request){
    //     $dateNow=Suplly::whereDate('created_at',$request->created_at)->get();
    //     return response()->json($dateNow);
    // }

    public function index()
    {
        $supply=Suplly::all();
        return response()->json([
            'data'=>$supply
        ],201);
    }


    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'nama'          => 'required|string|max:255',
            'kode_barang' => 'required|string|max:255',
            'total_all' => 'required|integer|max:255',
        ],[
            'nama.required'=>'Nama wajib di isi',
            'nama.string'=>'Nama berupa huruf bukan angka',
            'kode_barang.required'=>'Kode barang wajib di isi',
            'total_all.required'=>'Jumlah keseluruhan wajib di isi',
            'total_all.integer'=>'Jumlah keseluruhan menggunakan angka'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $suplly = Suplly::create([
            'nama' => $request->nama,
            'kode_barang' => $request->kode_barang,
            'total_all' => $request->total_all
        ]);

        return response()->json([
            'data'=>$suplly,
            'message'=>'Suplly berhasil di simpan'
        ],201);
    }
    public function show($id){
        $data = Suplly::where('id',$id)->first();
        return response()->json([
            'data'=>$data,
            'message'=>'Suplly sudah ditemukan',
        ],201);
    }
    public function update(Request $request,Suplly $id)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'kode_barang' => 'required|string|max:255',
            'total_all' => 'required|string|max:255',
        ],[
            'total_all.integer'=>'Jumlah keseluruhan menggunakan angka'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $id->update($request->all());

        return response()->json($id);
    }
    public function delete(Suplly $id)
    {
        $id->delete();
        return response()->json(['message' => 'Data Berhasil di hapus']);
    }
}
