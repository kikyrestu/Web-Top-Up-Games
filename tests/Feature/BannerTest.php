<?php
namespace Tests\Feature;
use App\Models\User;
use App\Models\Banner;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_save_html_banner()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($user)->post('/admin/banners', [
            'title' => 'Test Promo',
            'link' => null,
            'media_type' => 'html',
            'media_content' => '<div class="test">Hello</div>',
            'is_active' => '1',
            'order' => 1
        ]);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('banners', [
            'media_type' => 'html',
            'title' => 'Test Promo'
        ]);
        
        $banner = Banner::where('title', 'Test Promo')->first();
        $this->assertNotNull($banner);
        $this->assertSame('<div class="test">Hello</div>', $banner->media_content);
    }
}
