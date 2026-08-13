<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'category_id')) {
                $table->integer('category_id')->unsigned()->nullable()->after('team_name');
                $table->foreign('category_id')->references('id')->on('project_categories')->nullOnDelete();
            }

            if (! Schema::hasColumn('projects', 'team_id')) {
                $table->integer('team_id')->unsigned()->nullable()->after('category_id');
                $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'team_id')) {
                $table->dropForeign(['team_id']);
                $table->dropColumn('team_id');
            }

            if (Schema::hasColumn('projects', 'category_id')) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            }
        });
    }
};
