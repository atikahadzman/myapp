<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::all();

        return response()->json($books);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                // 'cover_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
                'cover_image' => 'required|string',
                'author' => 'required|string|max:255',
                'description' => 'required',
                // 'book_url' => 'required|file|mimes:pdf|max:10240',
                'book_url' => 'required|string',
                'total_pages' => 'required|integer',
            ]);

            $validated['user_id'] = $request->user()->id;

            $book = Book::create($validated);

            if ($request->hasFile('cover_image')) {
                $book->addMediaFromRequest('cover_image')
                    ->toMediaCollection('cover_image');
            }

            if ($request->hasFile('book_url')) {
                $book->addMediaFromRequest('book_url')
                    ->toMediaCollection('book_url');
            }

            return response()->json([
                'status' => 'success',
                'data' => $book
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'status' => 'error',
                'message' => 'Book not found'
            ], 404);
        }

        return response()->json($book);
    }

    public function update(Request $request, string $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'status' => 'error',
                'message' => 'Book not found'
            ], 404);
        }

        $data = $request->only([
            'title',
            'author',
            'description',
            'cover_image',
            'book_url',
            'total_pages',
        ]);

        $book->update($data);

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function destroy(string $id)
    {
        $books = Book::find($id);

        if (!$books) {
            return response()->json([
                'status' => 'error',
                'message' => 'Book not found'
            ], 404);
        }

        $books->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Book deleted successfully'
        ]);
    }
}
