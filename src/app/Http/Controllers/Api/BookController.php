<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Services\BookService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    public function __construct(
        protected BookService $bookService,
        protected ApiResponse $response,
    ) {}

    public function index(): JsonResponse
    {
        $books = $this->bookService->listBooks();

        return $this->response->collection(BookResource::collection($books));
    }

    public function show(int $id): JsonResponse
    {
        $book = $this->bookService->getBook($id);

        return $this->response->success(new BookResource($book));
    }

    public function store(StoreBookRequest $request): JsonResponse
    {
        $book = $this->bookService->createBook($request->validated());

        return $this->response->success(new BookResource($book), 'Book created successfully.', 201);
    }

    public function update(UpdateBookRequest $request, int $id): JsonResponse
    {
        $book = $this->bookService->updateBook($id, $request->validated());

        return $this->response->success(new BookResource($book), 'Book updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->bookService->deleteBook($id);

        return $this->response->success(null, 'Book deleted successfully.');
    }
}
