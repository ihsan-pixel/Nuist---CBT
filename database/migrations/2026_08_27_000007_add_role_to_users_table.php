<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('peserta')->after('password');
            });
        }

        if (Schema::hasColumn('users', 'is_admin')) {
            DB::table('users')->where('is_admin', true)->update(['role' => 'super_admin']);
            DB::table('users')->where('is_admin', false)->update(['role' => 'peserta']);
        } else {
            DB::table('users')->whereNull('role')->update(['role' => 'peserta']);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'is_admin')) {
            DB::table('users')->update(['is_admin' => DB::raw("CASE WHEN role = 'super_admin' THEN 1 ELSE 0 END")]);
        }

        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};
