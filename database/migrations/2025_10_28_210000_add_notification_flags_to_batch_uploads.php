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
        Schema::table('batch_uploads', function (Blueprint $table) {
            if (!Schema::hasColumn('batch_uploads', 'enable_sms')) {
                $table->boolean('enable_sms')->default(true)->after('email_template');
            }
            if (!Schema::hasColumn('batch_uploads', 'enable_email')) {
                $table->boolean('enable_email')->default(true)->after('enable_sms');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_uploads', function (Blueprint $table) {
            if (Schema::hasColumn('batch_uploads', 'enable_sms')) {
                $table->dropColumn('enable_sms');
            }
            if (Schema::hasColumn('batch_uploads', 'enable_email')) {
                $table->dropColumn('enable_email');
            }
        });
    }
};

