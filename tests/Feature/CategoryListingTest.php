<?php
namespace Tests\Feature;
use Tests\TestCase;

class CategoryListingTest extends TestCase
{
    public function test_top_up_game_page_renders(): void
    {
        $response = $this->get('/top-up-game');
        $this->assertNotEquals(500, $response->status(), 'Top Up Game returned 500');
        $response->assertStatus(200);
    }

    public function test_ppob_page_renders(): void
    {
        $response = $this->get('/ppob');
        $this->assertNotEquals(500, $response->status(), 'PPOB returned 500');
        $response->assertStatus(200);
    }
}
