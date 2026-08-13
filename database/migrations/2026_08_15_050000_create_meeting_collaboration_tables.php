<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_participant_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('meeting_id');
            $table->unsignedInteger('user_id');
            $table->foreign('meeting_id')->references('id')->on('meetings')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('event');
            $table->timestamps();
            $table->index(['meeting_id', 'created_at']);
        });

        Schema::create('meeting_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('meeting_id');
            $table->unsignedInteger('user_id');
            $table->foreign('meeting_id')->references('id')->on('meetings')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('type');
            $table->string('name');
            $table->string('path')->nullable();
            $table->string('url')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
            $table->index(['meeting_id', 'created_at']);
        });

        Schema::create('meeting_breakout_rooms', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('meeting_id');
            $table->unsignedInteger('task_id')->nullable();
            $table->unsignedInteger('created_by');
            $table->foreign('meeting_id')->references('id')->on('meetings')->cascadeOnDelete();
            $table->foreign('task_id')->references('id')->on('tasks')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('room_name')->unique();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['meeting_id']);
        });

        Schema::create('meeting_breakout_room_members', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('breakout_room_id');
            $table->unsignedInteger('user_id');
            $table->foreign('breakout_room_id')->references('id')->on('meeting_breakout_rooms')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['breakout_room_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_breakout_room_members');
        Schema::dropIfExists('meeting_breakout_rooms');
        Schema::dropIfExists('meeting_documents');
        Schema::dropIfExists('meeting_participant_logs');
    }
};
