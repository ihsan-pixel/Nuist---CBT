<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginPageBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_shows_nuist_cbt_branding(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('NUIST CBT LPMNU PWNU DIY', false);
        $response->assertSee('Sistem Computer Based Test resmi untuk pelaksanaan ujian.', false);
    }
}
