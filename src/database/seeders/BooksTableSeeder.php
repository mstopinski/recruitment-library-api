<?php

namespace Database\Seeders;

use App\Jobs\UpdateAuthorLastBook;
use App\Models\Author;
use App\Models\Book;
use Illuminate\Database\Seeder;

class BooksTableSeeder extends Seeder
{
    public function run(): void
    {
        $authors = Author::all();

        Book::factory()->count(20)->create()->each(function ($book) use ($authors) {
            $book->authors()->sync(
                $authors->random(rand(1, 3))->pluck('id')->toArray()
            );
        });

        $authors->each(function (Author $author) {
            UpdateAuthorLastBook::dispatch($author->id);
        });
    }
}
