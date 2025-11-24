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
        Schema::table('activity_logs', function (Blueprint $table) {
            // Add missing fields only if they don't exist already
            $addedEvent = false;
            if (!Schema::hasColumn('activity_logs', 'event')) {
                $table->string('event')->nullable()->after('action');
                $addedEvent = true;
            }

            $addedSubject = false;
            if (!Schema::hasColumn('activity_logs', 'subject_type')) {
                $table->string('subject_type')->nullable()->after('model_id');
                $addedSubject = true;
            }
            if (!Schema::hasColumn('activity_logs', 'subject_id')) {
                $table->unsignedBigInteger('subject_id')->nullable()->after('subject_type');
                $addedSubject = true;
            }

            $addedCauser = false;
            if (!Schema::hasColumn('activity_logs', 'causer_type')) {
                $table->string('causer_type')->nullable()->after('subject_id');
                $addedCauser = true;
            }
            if (!Schema::hasColumn('activity_logs', 'causer_id')) {
                $table->unsignedBigInteger('causer_id')->nullable()->after('causer_type');
                $addedCauser = true;
            }

            // Add indexes only when we added corresponding columns (avoid duplicate-index errors)
            if ($addedSubject) {
                $table->index(['subject_type', 'subject_id']);
            }
            if ($addedCauser) {
                $table->index(['causer_type', 'causer_id']);
            }
            if ($addedEvent) {
                $table->index('event');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // Drops guarded with existence checks to be safe across environments
            if (Schema::hasColumn('activity_logs', 'event')) {
                // Laravel generates index names; dropping by columns may fail if index wasn't created
                try { $table->dropIndex(['event']); } catch (\Throwable $e) {}
                $table->dropColumn('event');
            }
            if (Schema::hasColumn('activity_logs', 'causer_type') && Schema::hasColumn('activity_logs', 'causer_id')) {
                try { $table->dropIndex(['causer_type', 'causer_id']); } catch (\Throwable $e) {}
            }
            if (Schema::hasColumn('activity_logs', 'subject_type') && Schema::hasColumn('activity_logs', 'subject_id')) {
                try { $table->dropIndex(['subject_type', 'subject_id']); } catch (\Throwable $e) {}
            }
            if (Schema::hasColumn('activity_logs', 'causer_type')) {
                $table->dropColumn('causer_type');
            }
            if (Schema::hasColumn('activity_logs', 'causer_id')) {
                $table->dropColumn('causer_id');
            }
            if (Schema::hasColumn('activity_logs', 'subject_type')) {
                $table->dropColumn('subject_type');
            }
            if (Schema::hasColumn('activity_logs', 'subject_id')) {
                $table->dropColumn('subject_id');
            }
        });
    }
};