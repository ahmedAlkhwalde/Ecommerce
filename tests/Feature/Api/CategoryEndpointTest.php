<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use PHPUnit\Framework\Attributes\Test;

class CategoryEndpointTest extends TestCase
{
    #[Test]
    public function it_can_fetch_categories_list(): void
    {
        $user = User::first();

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/user/category');

        $response->dump();
        $response->assertStatus(200);
    }

    #[Test]
    public function it_can_create_a_new_category(): void
    {
        $user = User::first();

        $payload = [
            'name' => 'فئة تجريبية جديدة ' . rand(100, 999),
        ];

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/admin/category', $payload);

        $response->dump();

        $response->assertStatus(201);

        $response->assertJsonFragment(['name' => $payload['name']]);
    }
}