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
            // Add missing fields that ActivityLogController expects
            $table->string('event')->nullable()->after('action');
            $table->string('subject_type')->nullable()->after('model_id');
            $table->unsignedBigInteger('subject_id')->nullable()->after('subject_type');
            $table->string('causer_type')->nullable()->after('subject_id');
            $table->unsignedBigInteger('causer_id')->nullable()->after('causer_type');
            
            // Add indexes for the new fields
            $table->index(['subject_type', 'subject_id']);
            $table->index(['causer_type', 'causer_id']);
            $table->index('event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['event']);
            $table->dropIndex(['causer_type', 'causer_id']);
            $table->dropIndex(['subject_type', 'subject_id']);
            
            $table->dropColumn([
                'event',
                'subject_type', 
                'subject_id',
                'causer_type',
                'causer_id'
            ]);
        });
    }
};