<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rates;
use App\Models\User;

class RatesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rates = Rates::all();

        return response()->json($rates);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rating' => 'required|integer',
            'review' => 'required|string',
            'book_id' => 'required|integer',
        ]);

        $rates = Rates::create([
            'rating' => $validated['rating'],
            'review' => $validated['review'],
            'added_by' => $request->user()->id,
            'book_id' => $validated['book_id'],
        ]);

        return response()->json([
            'status' => 'success'
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $rate = Rates::findOrFail($id);

        if (!$rate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not found'
            ], 404);
        }

        return response()->json($rate);
    } 
    
    public function getByBookId(Request $request, string $id)
    {
        $rate = Rates::where('book_id', $id)
            ->where('added_by', $request->user()->id)
            ->first();

        if (!$rate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not found'
            ], 404);
        }

        return response()->json($rate);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rate = Rates::findOrFail($id);

        if (!$rate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rating not found'
            ], 404);
        }

        $data = $request->only([
            'rating',
            'review',
            // 'added_by',
            'book_id',
        ]);

        if ($rate->update($data)) {
            return response()->json([
                'status' => 'success',
            ], 200);
        }

         return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $rate = Rates::findOrFail($id);

        if (!$rate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not found'
            ], 404);
        }

        $rate->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'This rating deleted successfully'
        ], 200);
    }
}
