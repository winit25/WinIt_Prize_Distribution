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
        Schema::table('transactions', function (Blueprint $table) {
            $table->text('token')->nullable()->after('api_response'); // Electricity token
            $table->string('units')->nullable()->after('token'); // Units/KWh
            $table->string('order_id')->nullable()->after('buypower_reference'); // BuyPower order ID
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['token', 'units', 'order_id']);
        });
    }
};
