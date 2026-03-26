<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ErrorDetailTest extends TestCase
{
    use RefreshDatabase;
    public function test_artikel_exception(): void
    {
        try {
            $response = $this->withoutExceptionHandling()->get('/artikel');
        } catch (\Throwable $e) {
            $this->fail(get_class($e) . ': ' . $e->getMessage() . ' in ' . basename($e->getFile()) . ':' . $e->getLine());
        }
        $response->assertStatus(200);
    }

    public function test_kategori_exception(): void
    {
        $category = \App\Models\Category::create(['name' => 'Pulsa Reguler', 'slug' => 'pulsa-reguler', 'category_type' => 'ppob', 'is_active' => true]);
        try {
            $response = $this->withoutExceptionHandling()->get('/kategori/pulsa-reguler');
        } catch (\Throwable $e) {
            $this->fail(get_class($e) . ': ' . $e->getMessage() . ' in ' . basename($e->getFile()) . ':' . $e->getLine());
        }
        $response->assertStatus(200);
    }
}
