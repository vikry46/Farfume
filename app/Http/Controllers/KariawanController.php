<?php

namespace App\Http\Controllers;

use App\Models\Kariawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; 

class KariawanController extends Controller
{
    public function index(){
        $zee=Kariawan:: all();
        return response()->json([
            'data'=>$zee
        ],201);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'nik' => 'required|string|max:255|unique:kariawans,nik',
            'nama' => 'required|string|max:255',
            'kelamin' => ['required', Rule::in(['Laki-laki', 'Perempuan'])], 
            'jabatan' => 'required|string|max:255',
        ], [
            'nik.required' => 'NIK wajib diisi',
            'nik.unique' => 'NIK sudah digunakan, silakan gunakan NIK lain',
            'nama.required' => 'Nama wajib diisi',
            'kelamin.required' => 'Kelamin wajib diisi',
            'jabatan.required' => 'Jabatan wajib diisi',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        $christy = Kariawan::create([
            'nik' => $request->nik,
            'nama' => $request->nama,
            'kelamin' => $request->kelamin,
            'jabatan' => $request->jabatan,
        ]);
    
        return response()->json([
            'data' => $christy,
            'message' => 'Data berhasil disimpan'
        ], 201);
    }
    


    public function show($id){
        $Gits = Kariawan::where('id',$id)->first();
        return response()->json([
            'data'=>$Gits,
            'message'=>'Id berhasil di temukan',
        ],201);
    }


    public function update(Request $request, Kariawan $id) {
        $vikry = Validator::make($request->all(), [
            'nik' => 'required|string|max:255|unique:kariawans,nik,' . $id->id, // Unik kecuali data sendiri
            'nama' => 'required|string|max:255',
            'kelamin' => ['required', Rule::in(['Laki-laki', 'Perempuan'])], 
            'jabatan' => 'required|string|max:255',
        ], [
            'kelamin.required' => 'Kelamin wajib diisi',
            'kelamin.in' => 'Jenis kelamin harus Laki-laki atau Perempuan',
        ]); 

        if ($vikry->fails()) {
            return response()->json($vikry->errors(), 422);
        }

        $id->update($request->all());
        return response()->json([
            'data' => $id,
            'message' => 'Data berhasil diperbarui'
        ], 200);
    }


    public function delete(Kariawan $id){
        $id->delete();
        return response()->json(['message'=>'Data berhasil di hapus']);
    }

}
