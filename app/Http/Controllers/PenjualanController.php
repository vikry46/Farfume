<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Suplly;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class PenjualanController extends Controller
{
    public function index()
    {
        $penjualan = Penjualan ::with(['Market','Supplie'])->get();
        return response()->json([
            'success'   =>  true,
            'massage'   =>  'List semua pengiriman',
            'data'      =>  $penjualan
        ],Response::HTTP_OK);
    }
    public function show($id)
    {
        $penjualan = Penjualan :: with(['Market','Supplie'])->find($id);

        if (!$penjualan){
            return response()->json([
                'success'   =>  false,
                'massage'   =>  'Data tidak ditemukan',
            ],Response::HTTP_NOT_FOUND);
        }
        return response()->json([
            'succsess'  =>  true,
            'massage'   =>  'Detail pengirim',
            'data'      =>  $penjualan
        ],Response::HTTP_OK);
    }

        public function store(Request $request)
        {
            // dd($request->all());
            $request->validate([
                'id_market'     =>      'required|exists:markets,id',
                'id_supplie'    =>      'required|exists:supplies,id',
                'terjual'       =>      'required|integer|min:1',
                'tanggal'       =>      'required|date',
            ]);

            $penjualan = Penjualan :: create([
                'id_market'     =>  $request->id_market,
                'id_supplie'    =>  $request->id_supplie,
                'terjual'       =>  $request->terjual,
                'tanggal'       =>  $request->tanggal,
                'delete'        =>  false
            ]);

            return response()->json([
                'success'   =>  true,
                'message'   =>  'Pengiriman data di tambahkan',
                'data'      =>  $penjualan
            ], Response::HTTP_CREATED);
        }

    public function update(Request $request, $id)
    {
        $penjualan = Penjualan::find($id);
        if(!$penjualan){
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], Response::HTTP_NOT_FOUND);
        }
        $request->validate([
            'id_market'    =>   'exists:markets,id',
            'id_supplie'   =>   'exists:supplies,id',
            'terjual'      =>   'integer|min:1',
            'tanggal'      =>   'date'
        ]);

        $penjualan->update($request->all());
        return response()->json([
            'Success'   =>  true,
            'message'   =>  'Data berhasil diperbharui',
            'data'      =>  $penjualan
        ],Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $penjualan = Penjualan::find($id);
        if(!$penjualan){
            return response()->json([
                'succsess'  =>  false,
                'massage'   =>  'Data tidak ditemukan',
            ],Response::HTTP_NOT_FOUND);
        }
        $penjualan->delete();
        return response()->json([
            'succsess'  =>  true,
            'massage'   =>  'Data berhasil di hapus'
        ],Response::HTTP_OK);
    }
}
