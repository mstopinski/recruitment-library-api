<?php

namespace App\Services;

use App\Exceptions\ResourceNotFoundException;
use App\Models\Author;
use Illuminate\Pagination\LengthAwarePaginator;

class AuthorService
{
    public function listAuthors(?string $search = null): LengthAwarePaginator
    {
        $query = Author::with('books');

        if ($search) {
            $query->whereHas('books', function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%');
            });
        }

        return $query->latest()->paginate(10);
    }

    public function getAuthor(int $id): Author
    {
        return Author::with('books')->find($id)
            ?? throw new ResourceNotFoundException('Author', $id);
    }
}