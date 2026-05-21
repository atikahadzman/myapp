<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReadingProgress;
use App\Models\Book;

class ProgressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $progress = ReadingProgress::all();

        return response()->json($progress);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|integer',
            'current_pages' => 'required|integer',
            'bookmarks' => 'sometimes|array',
            'highlights' => 'sometimes|array'
        ]);

        $progress = ReadingProgress::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'book_id' => $validated['book_id'],
                'current_pages' => $validated['current_pages'],
            ],
            [
                'bookmarks' => $validated['bookmarks'] ?? [],
                'highlights' => $validated['highlights'] ?? [],
            ]
        );

        return response()->json([
            'status' => 'success'
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $progress = ReadingProgress::find($id);

        if (!$progress) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not found'
            ], 404);
        }

        return response()->json($progress);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $progress = ReadingProgress::find($id);

        if (!$progress) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not found'
            ], 404);
        }

        $data = $request->only([
            'current_pages',
            'bookmarks',
            'highlights',
        ]);

        $progress->update($data);

        return response()->json([
            'status' => 'success',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $progress = ReadingProgress::find($id);

        if (!$progress) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not found'
            ], 404);
        }

        $progress->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'This progress has been deleted.'
        ], 200);
    }

    public function getByUserId(Request $request)
    {
        $progress = ReadingProgress::with('book')
            ->where('user_id', $request->user_id)
            ->get();

        if (!$progress) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not found'
            ], 404);
        }

        return response()->json($progress);
    }
}
