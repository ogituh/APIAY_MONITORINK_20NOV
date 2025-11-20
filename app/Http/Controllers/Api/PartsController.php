<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Part;
use Illuminate\Http\Request;

class PartsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Part::all();

        return response()->json([
            'message' => 'success',
            'items' => $items,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'bpid' => 'required',
            'item' => 'required',
            'description' => 'required',
            'unit' => 'required',
        ]);

        $bpid = auth()->user()->bpid;

        $item = Part::create([
            'bpid' => $bpid,
            'item' => $request->item,
            'description' => $request->description,
            'unit' => $request->unit,
        ]);

        return response()->json([
            'message' => 'success',
            'item' => $item,
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
