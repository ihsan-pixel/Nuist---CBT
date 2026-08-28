<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SebConfigDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_double_compressed_seb_configuration_with_valid_start_url(): void
    {
        Config::set('app.url', 'https://cbt.nuist.id');

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Exam::query()->create([
            'title' => 'CBT Demo',
            'description' => 'Ujian contoh untuk mode penguncian browser.',
            'duration_minutes' => 60,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('exam.seb-config'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/seb');
        $response->assertHeader('Content-Disposition', 'attachment; filename="cbt-demo.seb"');

        $sebContent = $response->getContent();
        $this->assertNotEmpty($sebContent);

        $outerData = gzdecode($sebContent);
        $this->assertNotFalse($outerData);
        $this->assertSame('plnd', substr($outerData, 0, 4));

        $innerGzip = substr($outerData, 4);
        $plistXml = gzdecode($innerGzip);

        $this->assertNotFalse($plistXml);
        $this->assertStringStartsWith('<?xml', ltrim($plistXml));
        $this->assertStringContainsString('<key>startURL</key>', $plistXml);
        $this->assertStringContainsString('<string>https://cbt.nuist.id/exam/seb</string>', $plistXml);

        $dom = new \DOMDocument();
        $this->assertTrue($dom->loadXML($plistXml));
    }
}
