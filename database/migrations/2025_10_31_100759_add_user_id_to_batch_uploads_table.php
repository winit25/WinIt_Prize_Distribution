<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, check if column already exists (supports MySQL and SQLite)
        $columnExists = false;
        try {
            $driver = DB::connection()->getDriverName();
            
            if ($driver === 'sqlite') {
                $columns = DB::select("PRAGMA table_info(`batch_uploads`)");
                foreach ($columns as $column) {
                    if ($column->name === 'user_id') {
                        $columnExists = true;
                        break;
                    }
                }
            } else {
                // MySQL/MariaDB
                $columns = DB::select("SHOW COLUMNS FROM `batch_uploads` LIKE 'user_id'");
                $columnExists = count($columns) > 0;
            }
        } catch (\Exception $e) {
            $columnExists = false;
        }
        
        if (!$columnExists) {
            Schema::table('batch_uploads', function (Blueprint $table) {
                if (DB::connection()->getDriverName() === 'sqlite') {
                    // SQLite doesn't support 'after' clause
                    $table->unsignedBigInteger('user_id')->nullable();
                } else {
                    $table->unsignedBigInteger('user_id')->nullable()->after('status');
                }
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_uploads', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
