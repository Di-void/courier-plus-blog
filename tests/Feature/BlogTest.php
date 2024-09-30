<?php

namespace Tests\Feature;

use App\Models\Blog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    // stubs
    private $headers;
    private $user_id;

    protected function setUp(): void
    {
        parent::setUp();

        $token = env('AUTH_TOKEN', 'CourierPlus@321');

        $this->headers = [
            'Authorization' => "Bearer {$token}"
        ];

        // seeded test user
        $this->user_id = 1;
    }

    public function test_can_fetch_all_blogs(): void
    {
        // ACT
        $response = $this->withHeaders($this->headers)->getJson('/api/v1/blogs');

        // ASSERT
        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ]);
    }

    public function test_can_create_a_blog(): void
    {
        // ARRANGE
        $data = [
            'title' => 'New Blog',
            'description' => 'Talk about courierplus',
        ];

        // ACT
        $response = $this->withHeaders($this->headers)->postJson('/api/v1/blogs', $data);

        // ASSERT
        $response
            ->assertStatus(201)
            ->assertJson(['message' => 'success'])
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('blogs', $data);
    }

    public function test_can_fetch_a_single_blog(): void
    {
        // ARRANGE
        $blog = Blog::factory()->create(['user_id' => $this->user_id]);

        // ACT
        $response = $this->withHeaders($this->headers)->getJson("/api/v1/blogs/{$blog->id}");

        // ASSERT
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'title',
                    'description',
                    'posts' => ['*' => ['title', 'content', 'image_uri']]
                ]
            ]);
    }

    public function test_can_update_a_blog()
    {
        $blog = Blog::factory()->create(['user_id' => $this->user_id]);

        // ARRANGE
        $data = [
            'title' => 'Courier Blog',
            'description' => 'Talk about courierplus',
        ];

        // ACT
        $response = $this->withHeaders($this->headers)->putJson("/api/v1/blogs/{$blog->id}", $data);

        // ASSERT
        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'success'])
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('blogs', $data);
    }

    public function test_can_delete_a_blog()
    {
        // ARRANGE
        $blog = Blog::factory()->create(['user_id' => $this->user_id]);

        // ACT
        $response = $this->withHeaders($this->headers)->deleteJson("/api/v1/blogs/{$blog->id}");

        // ASSERT
        $response
            ->assertStatus(200)
            ->assertExactJson(['message' => 'success', 'data' => null]);

        $this->assertDatabaseMissing('blogs', $blog->toArray());
    }
}