<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('issue_type')->default('task')->after('name');
            $table->unsignedInteger('assignee_id')->nullable()->after('user_id');
            $table->date('due_date')->nullable()->after('priority');
            $table->decimal('story_points', 5, 1)->nullable()->after('due_date');
            $table->string('labels', 1000)->nullable()->after('story_points');
            $table->string('sprint')->nullable()->after('labels');
            $table->foreign('assignee_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['assignee_id']);
            $table->dropColumn(['issue_type', 'assignee_id', 'due_date', 'story_points', 'labels', 'sprint']);
        });
    }
};