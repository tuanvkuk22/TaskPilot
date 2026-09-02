<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worklogs', function (Blueprint $table) {
            $table->unsignedInteger('assignee_id')->nullable()->after('task_id');
            $table->dateTime('started_at')->nullable()->after('hours');
            $table->dateTime('ended_at')->nullable()->after('started_at');

            $table->foreign('assignee_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['task_id', 'assignee_id']);
            $table->index('started_at');
        });

        // Make legacy columns nullable so we can switch to started_at/ended_at model
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE worklogs MODIFY logged_at DATE NULL');
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE worklogs MODIFY logged_at DATE NOT NULL');

        Schema::table('worklogs', function (Blueprint $table) {
            $table->dropForeign(['assignee_id']);
            $table->dropIndex(['task_id', 'assignee_id']);
            $table->dropIndex(['started_at']);
            $table->dropColumn(['assignee_id', 'started_at', 'ended_at']);
        });
    }
};
