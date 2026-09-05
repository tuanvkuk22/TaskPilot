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
        if (!Schema::hasTable('user_skills')) {
            Schema::create('user_skills', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->string('skill_name');
                $table->unsignedTinyInteger('proficiency_level')->default(3); // 1-5
                $table->unsignedSmallInteger('years_experience')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'skill_name']);
            });
        }

        if (!Schema::hasTable('task_skills')) {
            Schema::create('task_skills', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('task_id')->unsigned();
                $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
                $table->string('skill_name');
                $table->timestamps();
                $table->unique(['task_id', 'skill_name']);
            });
        }

        if (!Schema::hasTable('ai_assignee_recommendations')) {
            Schema::create('ai_assignee_recommendations', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('task_id')->unsigned()->nullable();
                $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->decimal('score', 6, 4)->default(0.0000);
                $table->unsignedInteger('rank_position')->default(1);
                $table->json('match_reasons')->nullable();
                $table->string('status')->default('pending'); // pending, accepted, rejected, changed
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_assignee_recommendations');
        Schema::dropIfExists('task_skills');
        Schema::dropIfExists('user_skills');
    }
};
