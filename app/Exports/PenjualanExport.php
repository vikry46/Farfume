<?php

namespace App\Exports;

use App\Models\Penjualan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PenjualanExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Ambil semua data penjualan beserta relasi market dan supplie
        return Penjualan::with(['Market', 'Supplie'])->get()->map(function($item) {
            return [
                'Market'       => $item->Market->nama ?? '-',
                'Supplie'      => $item->Supplie->nama ?? '-',
                'Terjual (ml)' => $item->terjual,
                'Estimasi Botol' => $item->estimasi_botol,
                'Ukuran Botol' => $item->ukuran_botol,
                'Harga'        => $item->harga,
                'Tanggal'      => $item->tanggal,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Market',
            'Supplie',
            'Terjual (ml)',
            'Estimasi Botol',
            'Ukuran Botol',
            'Harga',
            'Tanggal',
        ];
    }
}
