<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\UploadHistory; // ✅ Tambahkan ini
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StocksImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StocksImportController extends Controller
{
    public function importView()
    {

        $user = Auth::user();

        $stocks = Stock::with(['supplier', 'part'])->where('bpid', $user->bpid)->get();

        $histories = UploadHistory::where('bpid', $user->bpid)
            ->orderBy('uploaded_at', 'desc')
            ->get();

        return view('import', [
            'stocks' => $stocks,
            'histories' => $histories,
            'user' => $user,
        ]);
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ]);

        $user = Auth::user();

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Jalankan import
            Excel::import(new StocksImport, $request->file('file'));

            $tes = UploadHistory::create([
                'bpid'     => $user->bpid,
                'file_name'   => $request->file('file')->getClientOriginalName(),
                'uploaded_at' => now(),
            ]);


            return redirect()->back()->with('success', 'Data berhasil diimpor ke database!');
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->withErrors(['error' => 'Gagal mengimpor data: ' . $e->getMessage()]);
        }
    }
}
