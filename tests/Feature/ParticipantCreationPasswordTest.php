<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ParticipantCreationPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_generated_participant_password_uses_participant_code_plus_nuist(): void
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'role' => UserRole::SuperAdmin,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Peserta Baru',
        ]);

        $response->assertSessionHas('status', 'Peserta ujian berhasil dibuat.');

        $participant = User::query()
            ->where('role', UserRole::Peserta)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame($participant->participant_code.'@cbt.nuist.id', $participant->email);
        $this->assertTrue(Hash::check($participant->participant_code.'nuist', $participant->password));
    }
}
