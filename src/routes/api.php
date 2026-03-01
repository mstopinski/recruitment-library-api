<?php

use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BookController;
use Illuminate\Support\Facades\Route;

Route::get('books', [BookController::class, 'index']);
Route::get('books/{id}', [BookController::class, 'show'])->whereNumber('id');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('books', [BookController::class, 'store']);
    Route::put('books/{id}', [BookController::class, 'update'])->whereNumber('id');
    Route::delete('books/{id}', [BookController::class, 'destroy'])->whereNumber('id');
});

Route::get('authors', [AuthorController::class, 'index']);
Route::get('authors/{id}', [AuthorController::class, 'show'])->whereNumber('id');
