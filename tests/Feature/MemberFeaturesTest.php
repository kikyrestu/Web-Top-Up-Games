<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PaymentGateway;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_dashboard_renders()
    {
        $user = User::factory()->create(['is_verified' => true]);
        
        $response = $this->actingAs($user)->get(route('member.dashboard'));
        $response->assertStatus(200);
    }

    public function test_user_can_toggle_favorite_game()
    {
        $user = User::factory()->create(['is_verified' => true]);
        $category = Category::create([
            'name' => 'Mobile Legends',
            'slug' => 'mobile-legends',
            'thumbnail' => 'test.png',
        ]);
        
        // Add favorite
        $response = $this->actingAs($user)->post(route('member.favorites.toggle', $category->id));
        $response->assertJson(['success' => true, 'is_favorite' => true]);
        $this->assertDatabaseHas('favorite_games', [
            'user_id' => $user->id,
            'category_id' => $category->id
        ]);

        // Remove favorite
        $response2 = $this->actingAs($user)->post(route('member.favorites.toggle', $category->id));
        $response2->assertJson(['success' => true, 'is_favorite' => false]);
        $this->assertDatabaseMissing('favorite_games', [
            'user_id' => $user->id,
            'category_id' => $category->id
        ]);
    }
}
