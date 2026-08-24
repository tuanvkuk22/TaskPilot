<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Project Documents table
        Schema::create('project_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('user_id');
            $table->string('title');
            $table->string('url');
            $table->text('description')->nullable();
            $table->string('type')->default('link'); // link, file, google_doc, notion, confluence
            $table->timestamps();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Project Forms table
        Schema::create('project_forms', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('user_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('fields'); // [{label, type, required, options}]
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Project Form Responses table
        Schema::create('project_form_responses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('form_id');
            $table->unsignedInteger('user_id');
            $table->json('data'); // {field_label => value}
            $table->timestamps();
            $table->foreign('form_id')->references('id')->on('project_forms')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Project Development Links table
        Schema::create('project_development_links', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('user_id');
            $table->string('type'); // branch, commit, pr, pipeline, deployment
            $table->string('label');
            $table->string('url');
            $table->string('status')->nullable(); // open, merged, closed, failed, success
            $table->timestamp('linked_at')->nullable();
            $table->timestamps();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_development_links');
        Schema::dropIfExists('project_form_responses');
        Schema::dropIfExists('project_forms');
        Schema::dropIfExists('project_documents');
    }
};
