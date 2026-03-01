<?php

namespace App\Services;

use App\Exceptions\ResourceNotFoundException;
use App\Jobs\UpdateAuthorLastBook;
use App\Models\Author;
use App\Models\Book;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BookService
{
    public function listBooks(): LengthAwarePaginator
    {
        return Book::with('authors')->latest()->paginate(10);
    }

    public function getBook(int $id): Book
    {
        return Book::with('authors')->find($id)
            ?? throw new ResourceNotFoundException('Book', $id);
    }

    public function createBook(array $data): Book
    {
        $book = DB::transaction(function () use ($data) {
            $book = Book::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
            ]);

            $book->authors()->sync($data['author_ids']);
            $book->load('authors');

            return $book;
        });

        foreach ($book->authors as $author) {
            UpdateAuthorLastBook::dispatch($author->id);
        }

        return $book;
    }

    public function updateBook(int $id, array $data): Book
    {
        $book = Book::find($id)
            ?? throw new ResourceNotFoundException('Book', $id);

        $existingAuthorIds = array_key_exists('author_ids', $data)
            ? $book->authors()->pluck('authors.id')->toArray()
            : [];

        DB::transaction(function () use ($book, $data) {
            if (array_key_exists('title', $data)) {
                $book->title = $data['title'];
            }

            if (array_key_exists('description', $data)) {
                $book->description = $data['description'];
            }

            $book->save();

            if (array_key_exists('author_ids', $data)) {
                $book->authors()->sync($data['author_ids']);
            }
        });

        $book->load('authors');

        if (array_key_exists('author_ids', $data)) {
            $newAuthors = $book->authors->filter(
                fn (Author $author) => !in_array($author->id, $existingAuthorIds)
            );

            foreach ($newAuthors as $author) {
                UpdateAuthorLastBook::dispatch($author->id);
            }

            $removedAuthorIds = array_diff($existingAuthorIds, $data['author_ids']);
            foreach ($removedAuthorIds as $removedAuthorId) {
                UpdateAuthorLastBook::dispatch($removedAuthorId);
            }
        }

        return $book;
    }

    public function deleteBook(int $id): void
    {
        $book = Book::find($id)
            ?? throw new ResourceNotFoundException('Book', $id);

        $authorIds = $book->authors()->pluck('authors.id')->toArray();
        $book->delete();

        foreach ($authorIds as $authorId) {
            UpdateAuthorLastBook::dispatch($authorId);
        }
    }
}
