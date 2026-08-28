<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRedesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_new_summary_sections(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'role' => UserRole::SuperAdmin,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Selamat datang,', false);
        $response->assertSee('Profil Akun', false);
        $response->assertSee('Ujian Aktif', false);
        $response->assertSee('Keamanan SEB', false);
        $response->assertSee('Mulai dengan SEB', false);
        $response->assertSee('Informasi Penting', false);
        $response->assertSee('NUIST CBT', false);
    }
}
