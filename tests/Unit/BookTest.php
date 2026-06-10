<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Book;
use App\Models\User;

class BookTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        $this->assertTrue(true);
    }

    public function test_book_created()
    {
        Storage::fake('public');

        // added_by is a foreign key, need to create a factory
        $user = User::factory()->create();

        $book = Book::create([
            'title' => 'Test',
            'author' => 'Test',
            'description' => 'Test',
            'total_pages' => 55,
            'added_by' => $user->id,
            'status' => Book::STATUS_ENABLE,
            'cover_image' => UploadedFile::fake()->image('cover.jpg'),
            'book_url' => UploadedFile::fake()->create('book.pdf', 100, 'application/pdf'),
        ]);

        $this->assertDatabaseHas('books', [
            'status' => Book::STATUS_ENABLE,
            'added_by' => $user->id,
        ]);
    }
}
