<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Rates;
use App\Models\User;

class RatesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page = request()->get('page', 1);
        $perPage = 10;

        $cacheKey = "rates_page_{$page}";

        $rates = cache()->remember($cacheKey, 60, function () use ($perPage, $page) {
            return Rates::select('rating', 'review', 'added_by', 'book_id')
                ->orderBy('created_at', 'desc')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->toArray();
        });

        return response()->json([
            'status' => 'success',
            'data' => $rates
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rating' => 'required|integer',
            'review' => 'required|string|max:255',
            'book_id' => 'required|integer',
        ]);

        $rates = Rates::create([
            'rating' => $validated['rating'],
            'review' => $validated['review'],
            'added_by' => $request->user()->id,
            'book_id' => $validated['book_id'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Rating created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $rate = Rates::find($id);

        if (!$rate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rating not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $rate
        ], 200);
    } 
    
    public function getSelfRateByBookId(Request $request, int $id)
    {
        $rate = Rates::where('book_id', $id)
            ->where('added_by', $request->user()->id)
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => $rate
        ], 200);
    }

     public function getByBookId(int $id)
    {
        $rate = Rates::with('user')
                ->where('book_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

        if (!$rate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rating not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $rate
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $rate = Rates::find($id);

        if (!$rate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rating not found'
            ], 404);
        }

        $validated = $request->validate([
            'rating' => 'sometimes|integer',
            'review' => 'sometimes|string|max:255',
            'book_id' => 'sometimes|integer',
        ]);

        $rate->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Rating updated successfully',
            'data' => $rate
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $rate = Rates::find($id);

        if (!$rate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rating not found'
            ], 404);
        }

        $rate->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'This rating deleted successfully'
        ], 200);
    }
}
