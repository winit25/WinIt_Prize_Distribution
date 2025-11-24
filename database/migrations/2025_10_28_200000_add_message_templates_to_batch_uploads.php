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
            if (!Schema::hasColumn('batch_uploads', 'sms_template')) {
                $table->text('sms_template')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('batch_uploads', 'email_template')) {
                $table->text('email_template')->nullable()->after('sms_template');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_uploads', function (Blueprint $table) {
            if (Schema::hasColumn('batch_uploads', 'sms_template')) {
                $table->dropColumn('sms_template');
            }
            if (Schema::hasColumn('batch_uploads', 'email_template')) {
                $table->dropColumn('email_template');
            }
        });
    }
};
