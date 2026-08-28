<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->latest()->paginate(15),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $participantCode = $this->generateParticipantCode();
        $password = $participantCode.'nuist';

        User::query()->create([
            'name' => $data['name'],
            'participant_code' => $participantCode,
            'email' => $participantCode.'@cbt.nuist.id',
            'password' => $password,
            'role' => 'peserta',
            'email_verified_at' => now(),
        ]);

        return back()->with('status', 'Peserta ujian berhasil dibuat.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', 'in:super_admin,panitia,peserta'],
        ]);

        $user->update([
            'role' => $data['role'],
        ]);

        return back()->with('status', 'Role user berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->with('status', 'Akun sendiri tidak dapat dihapus.');
        }

        if ($user->isAdmin() && User::query()->where('role', 'super_admin')->count() <= 1) {
            return back()->with('status', 'Minimal harus ada satu super admin.');
        }

        $user->delete();

        return back()->with('status', 'User berhasil dihapus.');
    }

    private function generateParticipantCode(): string
    {
        do {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (User::query()->where('participant_code', $code)->exists());

        return $code;
    }
}
