<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\ReadingProgress;
use App\Models\Book;

class ProgressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page = request()->get('page', 1);
        $perPage = 10;

        $cacheKey = "progress_page_{$page}";

        $progress = cache()->remember($cacheKey, 60, function () use ($perPage, $page) {
            return ReadingProgress::orderBy('created_at', 'desc')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->toArray();
        });

        return response()->json([
            'status' => 'success',
            'data' => $progress
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|integer',
            'bookmark' => 'required|integer',
            'highlights' => 'sometimes|array'
        ]);

        $progress = ReadingProgress::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'book_id' => $validated['book_id'],
                'bookmark' => $validated['bookmark'],
                'last_read_at' => now(),
            ],
            [
                'highlights' => $validated['highlights'] ?? [],
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Bookmark created successfully'
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $progress = ReadingProgress::find($id);

        if (!$progress) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bookmark not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $progress
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $progress = ReadingProgress::find($id);

        if (!$progress) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bookmark not found'
            ], 404);
        }

        $data = $request->validate([
            'bookmark' => 'required|integer',
            'highlights' => 'sometimes|array'
        ]);
        $data['last_read_at'] = now();

        $progress->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Bookmark/Highlight updated successfully',
            'data' => $progress
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $progress = ReadingProgress::find($id);

        if (!$progress) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bookmark not found'
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
        $progress = ReadingProgress::with('book.media')
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $progress
        ], 200);
    }
}
