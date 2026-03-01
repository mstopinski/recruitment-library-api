<?php

namespace Tests\Feature;

use App\Jobs\UpdateAuthorLastBook;
use App\Models\Author;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------
    // POST /api/books
    // -------------------------------------------------------

    public function test_authenticated_user_can_create_book(): void
    {
        $user = User::factory()->create();
        $authors = Author::factory()->count(2)->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/books', [
            'title' => 'Test Book',
            'description' => 'A test description',
            'author_ids' => $authors->pluck('id')->toArray(),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Test Book')
            ->assertJsonPath('data.description', 'A test description')
            ->assertJsonCount(2, 'data.authors');

        $this->assertDatabaseHas('books', ['title' => 'Test Book']);
    }

    public function test_unauthenticated_user_cannot_create_book(): void
    {
        $author = Author::factory()->create();

        $response = $this->postJson('/api/books', [
            'title' => 'Test Book',
            'author_ids' => [$author->id],
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_create_book_requires_title(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/books', [
            'author_ids' => [$author->id],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors('title');
    }

    public function test_create_book_requires_at_least_one_author(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/books', [
            'title' => 'Test Book',
            'author_ids' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('author_ids');
    }

    public function test_create_book_validates_author_exists(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/books', [
            'title' => 'Test Book',
            'author_ids' => [999],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('author_ids.0');
    }

    public function test_create_book_without_description(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/books', [
            'title' => 'Book Without Description',
            'author_ids' => [$author->id],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Book Without Description')
            ->assertJsonPath('data.description', null);
    }

    public function test_create_book_returns_correct_json_structure(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/books', [
            'title' => 'Structure Test',
            'description' => 'Testing structure',
            'author_ids' => [$author->id],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'authors' => [
                        ['id', 'first_name', 'last_name'],
                    ],
                ],
            ]);
    }

    public function test_create_book_rejects_soft_deleted_author(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $author->delete();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/books', [
            'title' => 'Test Book',
            'author_ids' => [$author->id],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('author_ids.0');
    }

    public function test_create_book_dispatches_update_author_job(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $author = Author::factory()->create();

        $this->actingAs($user, 'sanctum')->postJson('/api/books', [
            'title' => 'Job Test Book',
            'author_ids' => [$author->id],
        ]);

        Queue::assertPushed(UpdateAuthorLastBook::class);
    }

    // -------------------------------------------------------
    // DELETE /api/books/{id}
    // -------------------------------------------------------

    public function test_authenticated_user_can_delete_book(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create();
        $book->authors()->attach($author);

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('books', ['id' => $book->id]);
    }

    public function test_unauthenticated_user_cannot_delete_book(): void
    {
        $book = Book::factory()->create();

        $response = $this->deleteJson("/api/books/{$book->id}");

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_delete_nonexistent_book_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->deleteJson('/api/books/999');

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_delete_book_dispatches_update_author_job(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create();
        $book->authors()->attach($author);

        $this->actingAs($user, 'sanctum')->deleteJson("/api/books/{$book->id}");

        Queue::assertPushed(UpdateAuthorLastBook::class);
    }
}
