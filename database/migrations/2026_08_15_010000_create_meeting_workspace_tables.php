<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('team_id')->nullable();
            $table->unsignedInteger('created_by');
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('room_name')->unique();
            $table->string('status')->default('scheduled');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'status']);
        });

        Schema::create('meeting_members', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('meeting_id');
            $table->unsignedInteger('user_id');
            $table->foreign('meeting_id')->references('id')->on('meetings')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('role')->default('participant');
            $table->json('permissions')->nullable();
            $table->timestamps();
            $table->unique(['meeting_id', 'user_id']);
        });

        Schema::create('meeting_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('meeting_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('source_task_id')->nullable();
            $table->foreign('meeting_id')->references('id')->on('meetings')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('source_task_id')->references('id')->on('tasks')->nullOnDelete();
            $table->text('body')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_mime')->nullable();
            $table->unsignedBigInteger('attachment_size')->nullable();
            $table->timestamps();
            $table->index(['meeting_id', 'created_at']);
        });

        Schema::create('meeting_message_mentions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('message_id');
            $table->unsignedInteger('user_id');
            $table->foreign('message_id')->references('id')->on('meeting_messages')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['message_id', 'user_id']);
        });

        Schema::create('meeting_notes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('meeting_id');
            $table->unsignedInteger('user_id');
            $table->foreign('meeting_id')->references('id')->on('meetings')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->longText('body');
            $table->timestamps();
            $table->unique(['meeting_id', 'user_id']);
        });

        Schema::create('meeting_minutes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('meeting_id');
            $table->unsignedInteger('created_by');
            $table->foreign('meeting_id')->references('id')->on('meetings')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->longText('content')->nullable();
            $table->longText('decisions')->nullable();
            $table->timestamps();
            $table->unique('meeting_id');
        });

        Schema::create('meeting_minute_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('minute_id');
            $table->unsignedInteger('task_id')->nullable();
            $table->unsignedInteger('assignee_id')->nullable();
            $table->foreign('minute_id')->references('id')->on('meeting_minutes')->cascadeOnDelete();
            $table->foreign('task_id')->references('id')->on('tasks')->nullOnDelete();
            $table->foreign('assignee_id')->references('id')->on('users')->nullOnDelete();
            $table->string('title');
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        Schema::create('meeting_recordings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('meeting_id');
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('uploaded_by');
            $table->foreign('meeting_id')->references('id')->on('meetings')->cascadeOnDelete();
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('users')->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_recordings');
        Schema::dropIfExists('meeting_minute_actions');
        Schema::dropIfExists('meeting_minutes');
        Schema::dropIfExists('meeting_notes');
        Schema::dropIfExists('meeting_message_mentions');
        Schema::dropIfExists('meeting_messages');
        Schema::dropIfExists('meeting_members');
        Schema::dropIfExists('meetings');
    }
};