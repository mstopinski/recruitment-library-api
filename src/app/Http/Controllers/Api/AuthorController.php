<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthorResource;
use App\Services\AuthorService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function __construct(
        protected AuthorService $authorService,
        protected ApiResponse $response,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $authors = $this->authorService->listAuthors($request->query('search'));

        return $this->response->collection(AuthorResource::collection($authors));
    }

    public function show(int $id): JsonResponse
    {
        $author = $this->authorService->getAuthor($id);

        return $this->response->success(new AuthorResource($author));
    }
}
