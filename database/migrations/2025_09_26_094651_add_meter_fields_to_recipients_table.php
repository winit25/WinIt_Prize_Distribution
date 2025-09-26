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
        Schema::table('recipients', function (Blueprint $table) {
            $table->string('meter_number')->after('disco'); // Meter number
            $table->enum('meter_type', ['prepaid', 'postpaid'])->default('prepaid')->after('meter_number'); // Meter type
            $table->string('customer_name')->nullable()->after('name'); // Customer name (if different from recipient)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipients', function (Blueprint $table) {
            $table->dropColumn(['meter_number', 'meter_type', 'customer_name']);
        });
    }
};
