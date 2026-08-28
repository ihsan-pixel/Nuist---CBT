<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('exams', 'status')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->string('status')->default('published')->after('duration_minutes');
            });
        }

        if (! Schema::hasColumn('exam_sessions', 'expires_at')) {
            Schema::table('exam_sessions', function (Blueprint $table) {
                $table->timestamp('expires_at')->nullable()->after('started_at');
            });
        }

        if (! Schema::hasTable('exam_session_question_snapshots')) {
            Schema::create('exam_session_question_snapshots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('exam_session_id')->constrained()->cascadeOnDelete();
                $table->foreignId('exam_question_id')->constrained('exam_questions')->cascadeOnDelete();
                $table->unsignedInteger('sort_order')->default(0);
                $table->text('question_text');
                $table->json('option_snapshot');
                $table->string('selected_answer', 255)->nullable();
                $table->timestamps();

                $table->unique(['exam_session_id', 'exam_question_id'], 'ess_session_question_unique');
            });
        }

        DB::table('exams')->where('is_active', true)->update(['status' => 'published']);
        DB::table('exams')->where('is_active', false)->update(['status' => 'draft']);
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_session_question_snapshots');

        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
