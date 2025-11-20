<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StocksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($bpid)
    {
        $stocks = Stock::get()->where('bpid',$bpid);

        return response()->json([
            'message' => 'success',
            'stocks' => $stocks,
        ]);
    }
    public function allStocks()
    {
        $stocks = Stock::all();

        return response()->json([
            'message' => 'success',
            'stocks' => $stocks,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'bpid' => 'required',
            'part_no' => 'required',
            'item' => 'required',
            'quantity' => 'required',
            'insert_by' => 'required',
        ]);

        $user = Auth::user();

        if ($user->bpid !== $request->bpid) {
            return response()->json([
                'message' => 'Unauthorized: Anda tidak memiliki izin membuat order untuk BPID ini.'
            ], 403);
        }

        $bpid = auth()->user()->bpid;
        $insert_by = auth()->user()->username;

        $stock = Stock::create([
            'bpid' => $bpid,
            'part_no' => $request->part_no,
            'item' => $request->item,
            'quantity' => $request->quantity,
            'insert_date' => Carbon::now(),
            'insert_by' => $insert_by
        ]);

        return response()->json([
            'message' => 'success',
            'stock' => $stock,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
