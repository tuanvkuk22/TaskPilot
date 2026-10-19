<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'workflow_config')) {
                $table->json('workflow_config')->nullable()->after('project_type');
            }
        });

        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'test_result')) {
                $table->text('test_result')->nullable()->after('description');
            }
            if (!Schema::hasColumn('tasks', 'test_status')) {
                $table->string('test_status')->default('not_tested')->after('test_result');
            }
            if (!Schema::hasColumn('tasks', 'approved_by')) {
                $table->integer('approved_by')->unsigned()->nullable()->after('test_status');
            } else {
                $table->integer('approved_by')->unsigned()->nullable()->change();
            }
            if (!Schema::hasColumn('tasks', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('tasks', 'approval_signature')) {
                $table->string('approval_signature')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('tasks', 'approval_role')) {
                $table->string('approval_role')->nullable()->after('approval_signature');
            }
        });

        // Add foreign key in separate try block if needed
        try {
            Schema::table('tasks', function (Blueprint $table) {
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            });
        } catch (\Throwable $e) {
            // Ignore if foreign key already exists or cannot be created
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            try {
                $table->dropForeign(['approved_by']);
            } catch (\Throwable $e) {}

            $cols = ['test_result', 'test_status', 'approved_by', 'approved_at', 'approval_signature', 'approval_role'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('tasks', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'workflow_config')) {
                $table->dropColumn('workflow_config');
            }
        });
    }
};
