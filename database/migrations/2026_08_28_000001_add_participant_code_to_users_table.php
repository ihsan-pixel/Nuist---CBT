<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('participant_code')->nullable()->unique()->after('name');
        });

        DB::table('users')->orderBy('id')->lazyById()->each(function ($user) {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            while (DB::table('users')->where('participant_code', $code)->exists()) {
                $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update(['participant_code' => $code]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_participant_code_unique');
            $table->dropColumn('participant_code');
        });
    }
};
