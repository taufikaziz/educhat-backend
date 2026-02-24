<?php

namespace Tests\Feature;

use Tests\TestCase;

class ViewEncodingTest extends TestCase
{
    public function test_main_views_do_not_contain_common_mojibake_sequences(): void
    {
        $paths = [
            base_path('resources/views/chat.blade.php'),
            base_path('resources/views/welcome.blade.php'),
        ];

        $badMarkers = ['â', 'Ã', 'Â', 'ðŸ'];

        foreach ($paths as $path) {
            $content = file_get_contents($path);

            $this->assertNotFalse($content, "Failed reading {$path}");

            foreach ($badMarkers as $marker) {
                $this->assertStringNotContainsString(
                    $marker,
                    $content,
                    "Found mojibake marker '{$marker}' in {$path}"
                );
            }
        }
    }
}

