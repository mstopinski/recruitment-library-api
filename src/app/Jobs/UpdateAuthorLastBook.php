<?php

namespace App\Jobs;

use App\Models\Author;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class UpdateAuthorLastBook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(protected int $authorId) {}

    public function handle(): void
    {
        $author = Author::find($this->authorId);

        if (!$author) {
            return;
        }

        $latestBook = $author->books()
            ->latest('created_at')
            ->first();

        $author->update([
            'last_book_title' => $latestBook?->title,
        ]);
    }
}
