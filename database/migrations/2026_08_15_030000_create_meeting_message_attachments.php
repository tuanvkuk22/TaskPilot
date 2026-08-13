<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_message_attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('message_id');
            $table->foreign('message_id')->references('id')->on('meeting_messages')->cascadeOnDelete();
            $table->string('path');
            $table->string('name');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
            $table->index('message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_message_attachments');
    }
};