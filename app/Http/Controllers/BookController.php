<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with('media')->get();

        return response()->json($books->map(function ($book) {
            $media = $book->getFirstMedia('book_url');
            return [
                ...$book->toArray(),
                'book_url' => $media ? $media->id : null,
                'cover_image_url' => $book->getFirstMediaUrl('cover_image'),
            ];
        }));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'cover_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
                'author' => 'required|string|max:255',
                'description' => 'required',
                'book_url' => 'required|file|mimes:pdf|max:10240',
                'total_pages' => 'required|integer',
            ]);

            $book = Book::create([
                'title' => $request->title,
                'author' => $request->author,
                'description' => $request->description,
                'total_pages' => $request->total_pages,
                'added_by' => $request->user()->id,
            ]);

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
            'status' => 'success',
        ], 200);
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
        ], 200);
    }

    public function showPdf($id)
    {
        $file = Storage::path("public/8/file.pdf");

        return response()->file($file, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline'
        ]);
    }

    public function getBooksWithProgress(Request $request)
    {
        $books = Book::leftJoin(
            'reading_progress', 
            'books.id', 
            '=', 
            'reading_progress.book_id'
        )
        ->select(
            'books.*', 
            'reading_progress.bookmark', 
            'reading_progress.id as progress_id',
            'reading_progress.bookmark',
            'reading_progress.last_read_at',
            'books.total_pages', 
        )
        ->get();

        return $books;
    }
}
