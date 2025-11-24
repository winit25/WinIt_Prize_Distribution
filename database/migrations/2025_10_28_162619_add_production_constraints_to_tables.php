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
        // Add constraints to recipients table
        Schema::table('recipients', function (Blueprint $table) {
            // Add unique constraint for phone_number per batch (only if it doesn't exist)
            if (!$this->indexExists('recipients', 'unique_phone_per_batch')) {
                $table->unique(['batch_upload_id', 'phone_number'], 'unique_phone_per_batch');
            }
        });

        // Add constraints to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            // Add index for better performance (only if it doesn't exist)
            if (!$this->indexExists('transactions', 'transactions_status_created_at_index')) {
                $table->index(['status', 'created_at'], 'transactions_status_created_at_index');
            }
            if (!$this->indexExists('transactions', 'transactions_batch_status_index')) {
                $table->index(['batch_upload_id', 'status'], 'transactions_batch_status_index');
            }
        });

        // Add constraints to batch_uploads table
        Schema::table('batch_uploads', function (Blueprint $table) {
            // Add index for better performance (only if it doesn't exist)
            if (!$this->indexExists('batch_uploads', 'batch_uploads_status_created_at_index')) {
                $table->index(['status', 'created_at'], 'batch_uploads_status_created_at_index');
            }
            // Only add user_id index if the column exists
            if ($this->columnExists('batch_uploads', 'user_id') && !$this->indexExists('batch_uploads', 'batch_uploads_user_status_index')) {
                $table->index(['user_id', 'status'], 'batch_uploads_user_status_index');
            }
        });

        // Add constraints to users table
        Schema::table('users', function (Blueprint $table) {
            // Additional constraints can be added here if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove constraints from recipients table
        Schema::table('recipients', function (Blueprint $table) {
            $table->dropUnique('unique_phone_per_batch');
        });

        // Remove constraints from transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_status_created_at_index');
            $table->dropIndex('transactions_batch_status_index');
        });

        // Remove constraints from batch_uploads table
        Schema::table('batch_uploads', function (Blueprint $table) {
            $table->dropIndex('batch_uploads_status_created_at_index');
            $table->dropIndex('batch_uploads_user_status_index');
        });
    }

    /**
     * Check if an index exists on a table (supports MySQL and SQLite)
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $driver = DB::connection()->getDriverName();
            
            if ($driver === 'sqlite') {
                $indexes = DB::select("PRAGMA index_list(`{$table}`)");
                foreach ($indexes as $index) {
                    if ($index->name === $indexName) {
                        return true;
                    }
                }
                return false;
            } else {
                // MySQL/MariaDB
                $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
                return count($indexes) > 0;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if a column exists in a table (supports MySQL and SQLite)
     */
    private function columnExists(string $table, string $columnName): bool
    {
        try {
            $driver = DB::connection()->getDriverName();
            
            if ($driver === 'sqlite') {
                $columns = DB::select("PRAGMA table_info(`{$table}`)");
                foreach ($columns as $column) {
                    if ($column->name === $columnName) {
                        return true;
                    }
                }
                return false;
            } else {
                // MySQL/MariaDB
                $columns = DB::select("SHOW COLUMNS FROM `{$table}` LIKE ?", [$columnName]);
                return count($columns) > 0;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
};