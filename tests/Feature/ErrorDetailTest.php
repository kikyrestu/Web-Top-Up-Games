<?php
namespace Tests\Feature;
use Tests\TestCase;

class ErrorDetailTest extends TestCase
{
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
        try {
            $response = $this->withoutExceptionHandling()->get('/kategori/pulsa-reguler');
        } catch (\Throwable $e) {
            $this->fail(get_class($e) . ': ' . $e->getMessage() . ' in ' . basename($e->getFile()) . ':' . $e->getLine());
        }
        $response->assertStatus(200);
    }
}
