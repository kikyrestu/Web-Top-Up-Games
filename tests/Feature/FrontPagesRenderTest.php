<?php

namespace Tests\Feature;

use Tests\TestCase;

class FrontPagesRenderTest extends TestCase
{
    /**
     * Test homepage renders without 500
     */
    public function test_homepage_renders(): void
    {
        $response = $this->get('/');
        $this->assertNotEquals(500, $response->status(), 'Homepage returned 500: ' . $response->content());
        $response->assertStatus(200);
    }

    /**
     * Test cek-pesanan page renders
     */
    public function test_cek_pesanan_renders(): void
    {
        $response = $this->get('/cek-pesanan');
        $this->assertNotEquals(500, $response->status(), 'Cek Pesanan returned 500');
        $response->assertStatus(200);
    }

    /**
     * Test artikel page renders
     */
    public function test_artikel_renders(): void
    {
        $response = $this->get('/artikel');
        $this->assertNotEquals(500, $response->status(), 'Artikel returned 500');
        $response->assertStatus(200);
    }

    /**
     * Test static pages render (FAQ, Kontak, etc)
     */
    public function test_faq_page_renders(): void
    {
        $response = $this->get('/halaman/faq');
        $this->assertNotEquals(500, $response->status(), 'FAQ page returned 500');
    }

    public function test_kontak_page_renders(): void
    {
        $response = $this->get('/halaman/kontak');
        $this->assertNotEquals(500, $response->status(), 'Kontak page returned 500');
    }

    public function test_daftar_harga_renders(): void
    {
        $response = $this->get('/halaman/daftar-harga');
        $this->assertNotEquals(500, $response->status(), 'Daftar Harga returned 500');
    }

    /**
     * Test kategori/ppob page renders (show-ppob)
     */
    public function test_kategori_pulsa_reguler_renders(): void
    {
        // This uses show-ppob.blade.php
        $response = $this->get('/kategori/pulsa-reguler');
        $this->assertNotEquals(500, $response->status(), 'Kategori pulsa-reguler returned 500');
    }

    /**
     * Test login pages render
     */
    public function test_customer_login_renders(): void
    {
        $response = $this->get('/login');
        $this->assertNotEquals(500, $response->status(), 'Login returned 500');
    }

    /**
     * Test error pages render
     */
    public function test_404_page_renders(): void
    {
        $response = $this->get('/halaman-yang-tidak-ada-12345');
        $response->assertStatus(404);
    }
}
