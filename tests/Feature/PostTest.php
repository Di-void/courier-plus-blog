<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Post;
use App\Models\PostLike;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    // stubs
    private $headers;
    private $user_id;
    private array $blog;
    private array $post;
    private const RES_JSON_STRUCT = [
        'message',
        'data' => [
            'id',
            'title',
            'image_uri',
            'content',
            'likes_count',
            'comments' => [
                '*' => ['id', 'content', 'user_id']
            ]
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $token = env('AUTH_TOKEN', 'CourierPlus@321');

        $this->headers = [
            'Authorization' => "Bearer {$token}"
        ];

        // seeded test user
        $this->user_id = 1;

        $blog = Blog::factory()->create(['user_id' => $this->user_id]);
        $this->blog = $blog->toArray();

        $post = Post::factory()->create(['blog_id' => $blog->id, 'user_id' => $this->user_id]);
        $this->post = $post->toArray();
    }


    public function test_can_fetch_all_posts(): void
    {
        // ACT
        $response = $this->withHeaders($this->headers)->getJson('/api/v1/posts');

        // ASSERT
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => ['posts']
            ])
            ->assertJson([
                'message' => 'success',
            ]);
    }

    public function test_can_create_a_post(): void
    {
        // ARRANGE
        $data = [
            'title' => fake()->sentence(),
            'content' => fake()->paragraph(),
            'image_uri' => fake()->imageUrl(),
            'blog_id' => $this->blog['id'],
        ];

        // ACT
        $response = $this->withHeaders($this->headers)->postJson('/api/v1/posts', $data);

        unset($data['blog_id']);

        // ASSERT
        $response
            ->assertStatus(201)
            ->assertJson(['message' => 'success'])
            ->assertJsonStructure([
                'message',
                'data' => ['title', 'id', 'image_uri', 'content', 'updated_at', 'created_at']
            ])
            ->assertJsonFragment($data);
    }

    public function test_cannot_create_unassociated_post(): void
    {
        // ARRANGE
        $data = [
            'title' => fake()->sentence(),
            'content' => fake()->paragraph(),
            'image_uri' => fake()->imageUrl(),
            // random but surefire
            'blog_id' => 4
        ];

        // ACT
        $response = $this->withHeaders($this->headers)->postJson('/api/v1/posts', $data);

        unset($data['blog_id']);

        // ASSERT
        $response
            ->assertStatus(400)
            ->assertJson(['message' => 'error', 'error' => ['resource' => 'blogs']])
            ->assertJsonStructure([
                'message',
                'error'
            ]);
    }

    public function test_can_update_a_post(): void
    {
        // ARRANGE
        $data = [
            'title' => 'New Post',
            'content' => 'Talking about this New Post',
        ];

        $post_id = $this->post['id'];

        // ACT
        $response = $this->withHeaders($this->headers)->putJson("/api/v1/posts/{$post_id}", $data);

        // ASSERT
        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'success'])
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('posts', $data);
    }

    public function test_can_fetch_a_single_post(): void
    {
        // ARRANGE
        $post_id = $this->post['id'];

        // ACT
        $response = $this->withHeaders($this->headers)->getJson("/api/v1/posts/{$post_id}");

        // ASSERT
        $response
            ->assertStatus(200)
            ->assertJsonStructure(self::RES_JSON_STRUCT);
    }

    public function test_can_delete_a_post(): void
    {
        // ARRANGE
        $post_id = $this->post['id'];

        // ACT
        $response = $this->withHeaders($this->headers)->deleteJson("/api/v1/posts/{$post_id}");

        // ASSERT
        $response
            ->assertStatus(200)
            ->assertExactJson(['message' => 'success', 'data' => null]);

        $this->assertDatabaseMissing('posts', $this->post);
    }

    public function test_can_like_a_post(): void
    {
        // ARRANGE
        $post_id = $this->post['id'];

        // ACT
        $response = $this->withHeaders($this->headers)->postJson("/api/v1/posts/{$post_id}/like");

        // ASSERT
        $response
            ->assertStatus(200)
            ->assertExactJson(['message' => 'success', 'data' => null]);

        $this->assertDatabaseHas('post_likes', ['user_id' => $this->user_id, 'post_id' => $this->post['id']]);
    }

    public function test_cannot_double_like_a_post(): void
    {
        // ARRANGE
        $post_id = $this->post['id'];

        PostLike::factory()->create([
            'user_id' => $this->user_id,
            'post_id' => $post_id
        ]);

        // ACT
        $response = $this->withHeaders($this->headers)->postJson("/api/v1/posts/{$post_id}/like");

        // ASSERT
        $response
            ->assertStatus(403)
            ->assertExactJson(['message' => 'error', 'error' => ['message' => 'Duplicate likes not allowed', 'resource' => 'posts']]);

        $this->assertDatabaseCount('post_likes', 1);
    }

    public function test_can_comment_on_a_post(): void
    {
        // ARRANGE
        $post_id = $this->post['id'];
        $data = ['content' => fake()->sentence()];

        // ACT
        $response = $this->withHeaders($this->headers)->postJson("/api/v1/posts/{$post_id}/comment", $data);

        // ASSERT
        $response
            ->assertStatus(201)
            ->assertJsonStructure(['message', 'data' => ['id', 'content', 'user_id', 'created_at', 'updated_at']])
            ->assertJsonFragment($data);
    }
}
