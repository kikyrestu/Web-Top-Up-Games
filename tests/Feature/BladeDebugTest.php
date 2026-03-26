<?php

namespace Tests\Feature;

use Tests\TestCase;

class BladeDebugTest extends TestCase
{
    public function test_compile_all_front_views(): void
    {
        $blade = app('blade.compiler');
        $viewsDir = resource_path('views/front');
        $files = glob($viewsDir . '/**/*.blade.php') + glob($viewsDir . '/*.blade.php');
        
        foreach ($files as $file) {
            $relative = str_replace(resource_path('views/'), '', $file);
            $content = file_get_contents($file);
            
            try {
                $compiled = $blade->compileString($content);
                // Count if/endif in compiled output
                $ifCount = preg_match_all('/\<\?php if/', $compiled);
                $endifCount = preg_match_all('/\<\?php endif/', $compiled);
                echo "OK:    $relative (if:$ifCount endif:$endifCount)\n";
            } catch (\Throwable $e) {
                echo "ERROR: $relative - {$e->getMessage()}\n";
            }
        }
        
        // Also compile the layout
        $layoutContent = file_get_contents(resource_path('views/layouts/front.blade.php'));
        $compiled = $blade->compileString($layoutContent);
        $ifCount = preg_match_all('/\<\?php if/', $compiled);
        $endifCount = preg_match_all('/\<\?php endif/', $compiled);
        echo "\nLAYOUT: layouts/front.blade.php (if:$ifCount endif:$endifCount)\n";
        
        // Check for if(){} with curly braces inside compiled layout (these are raw PHP)
        $curlyIfCount = preg_match_all('/if\s*\([^)]*\)\s*\{/', $compiled);
        echo "LAYOUT raw PHP if(){}: $curlyIfCount\n";
        
        $this->assertTrue(true);
    }
    
    public function test_render_homepage(): void
    {
        $response = $this->get('/');
        if ($response->status() === 500) {
            $content = $response->getContent();
            // Extract error message
            if (preg_match('/ParseError.*?<\/p>/s', $content, $m)) {
                $this->fail('Homepage 500: ' . strip_tags($m[0]));
            }
            $this->fail('Homepage 500: ' . substr(strip_tags($content), 0, 500));
        }
        $response->assertStatus(200);
    }
}
