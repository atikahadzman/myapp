<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\ReadingProgress;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with('media')->orderBy('created_at', 'desc')->get();

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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'author' => 'required|string|max:255',
            'description' => 'required',
            'book_url' => 'required|file|mimes:pdf|max:10240',
            'total_pages' => 'required|integer',
            'status' => 'required|integer',
        ],  
        [
            'book_url.max' => 'The book size must not exceed 10 MB.',
        ]
        );

        $book = Book::create([
            'title' => $request->title,
            'author' => $request->author,
            'description' => $request->description,
            'total_pages' => $request->total_pages,
            'added_by' => $request->user()->id,
            'status' => $request->status ?? Book::STATUS_ENABLE,
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
    }

    public function show(int $id)
    {
        $book = Book::with('user')->findOrFail($id);

        if (!$book) {
            return response()->json([
                'status' => 'error',
                'message' => 'Book not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $book
        ], 200);
    }

    public function update(Request $request, int $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'status' => 'error',
                'message' => 'Book not found'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'author' => 'required|string|max:255',
            'description' => 'required',
            'book_url' => 'required|file|mimes:pdf|max:10240',
            'total_pages' => 'required|integer',
            'status' => 'required|integer',
            'status' => [
                'sometimes',
                'integer',
                Rule::in([
                    Book::STATUS_ENABLE,
                    Book::STATUS_DISABLE,
                ]),
            ],
        ], 
        [
            'book_url.max' => 'The book size must not exceed 10 MB.',
        ]
        );

        $book->update($validated);

        return response()->json([
            'status' => 'success',
            'data' => $book
        ], 200);
    }

    /**
     * if there's active reader, put notify to current reader
     * update book status to inactive
     */
    public function destroy(int $id)
    {
        $books = Book::find($id);

        if (!$books) {
            return response()->json([
                'status' => 'error',
                'message' => 'Book not found'
            ], 404);
        }

        $activeReaders = ReadingProgress::where('book_id', $id)
            ->where('last_read_at', '>=', now()->subMinutes(10))
            ->where('user_id', '!=', auth()->id())
            ->count();

        $message = 'Book deleted successfully';
        if ($activeReaders > 0) {
            $message = 'Book scheduled for removal. Active readers will be notified.';
        }

        $books->update([
            'status' => Book::STATUS_INACTIVE
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $message
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

    public function getBooksWithProgress(int $id)
    {
        $books = Book::leftJoin('reading_progress', function($join) use ($id) {
            $join->on('books.id', '=', 'reading_progress.book_id')
                ->where('reading_progress.user_id', '=', $id);
        })
        ->select(
            'books.*',
            'reading_progress.id as progress_id',
            'reading_progress.bookmark',
            'reading_progress.last_read_at',
            'reading_progress.user_id',
        )
        ->get();

        // return $books;

        return response()->json([
            'status' => 'success',
            'data' => $books
        ], 200);
    }
}
