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
            if (!Schema::hasColumn('projects', 'status')) {
                $table->string('status')->default('active')->after('days');
            }

            if (!Schema::hasColumn('projects', 'archived')) {
                $table->boolean('archived')->default(false)->after('status');
            }

            if (!Schema::hasColumn('projects', 'project_type')) {
                $table->string('project_type')->default('personal')->after('archived');
            }

            if (!Schema::hasColumn('projects', 'team_name')) {
                $table->string('team_name')->nullable()->after('project_type');
            }

            if (!Schema::hasColumn('projects', 'project_code')) {
                $table->string('project_code')->nullable()->after('team_name');
            }

            if (!Schema::hasColumn('projects', 'start_date')) {
                $table->date('start_date')->nullable()->after('project_code');
            }

            if (!Schema::hasColumn('projects', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }

            if (!Schema::hasColumn('projects', 'budget')) {
                $table->decimal('budget', 15, 2)->nullable()->after('end_date');
            }

            if (!Schema::hasColumn('projects', 'priority')) {
                $table->string('priority')->default('medium')->after('budget');
            }

            if (!Schema::hasColumn('projects', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('priority');
            }

            if (!Schema::hasColumn('projects', 'location')) {
                $table->string('location')->nullable()->after('customer_name');
            }
        });

        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'status')) {
                $table->string('status')->default('todo')->after('hours');
            }

            if (!Schema::hasColumn('tasks', 'priority')) {
                $table->string('priority')->default('medium')->after('status');
            }
        });

        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'permissions')) {
                $table->json('permissions')->nullable()->after('name');
            }
        });

        Schema::table('project_user', function (Blueprint $table) {
            if (!Schema::hasColumn('project_user', 'role')) {
                $table->string('role')->default('member')->after('user_id');
            }

            if (!Schema::hasColumn('project_user', 'permissions')) {
                $table->json('permissions')->nullable()->after('role');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('projects', 'archived')) {
                $table->dropColumn('archived');
            }

            if (Schema::hasColumn('projects', 'project_type')) {
                $table->dropColumn('project_type');
            }

            if (Schema::hasColumn('projects', 'team_name')) {
                $table->dropColumn('team_name');
            }

            if (Schema::hasColumn('projects', 'project_code')) {
                $table->dropColumn('project_code');
            }

            if (Schema::hasColumn('projects', 'start_date')) {
                $table->dropColumn('start_date');
            }

            if (Schema::hasColumn('projects', 'end_date')) {
                $table->dropColumn('end_date');
            }

            if (Schema::hasColumn('projects', 'budget')) {
                $table->dropColumn('budget');
            }

            if (Schema::hasColumn('projects', 'priority')) {
                $table->dropColumn('priority');
            }

            if (Schema::hasColumn('projects', 'customer_name')) {
                $table->dropColumn('customer_name');
            }

            if (Schema::hasColumn('projects', 'location')) {
                $table->dropColumn('location');
            }
        });

        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('tasks', 'priority')) {
                $table->dropColumn('priority');
            }
        });

        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'permissions')) {
                $table->dropColumn('permissions');
            }
        });

        Schema::table('project_user', function (Blueprint $table) {
            if (Schema::hasColumn('project_user', 'role')) {
                $table->dropColumn('role');
            }

            if (Schema::hasColumn('project_user', 'permissions')) {
                $table->dropColumn('permissions');
            }
        });
    }
};
