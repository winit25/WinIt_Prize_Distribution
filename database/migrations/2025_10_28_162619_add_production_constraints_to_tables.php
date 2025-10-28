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
            
            // Note: SQLite doesn't support CHECK constraints in ALTER TABLE
            // These constraints should be enforced at the application level
        });

        // Add constraints to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            // Note: SQLite doesn't support CHECK constraints in ALTER TABLE
            // These constraints should be enforced at the application level
            
            // Add index for better performance (only if it doesn't exist)
            if (!$this->indexExists('transactions', 'status_created_at_index')) {
                $table->index(['status', 'created_at'], 'status_created_at_index');
            }
            if (!$this->indexExists('transactions', 'batch_status_index')) {
                $table->index(['batch_upload_id', 'status'], 'batch_status_index');
            }
        });

        // Add constraints to batch_uploads table
        Schema::table('batch_uploads', function (Blueprint $table) {
            // Note: SQLite doesn't support CHECK constraints in ALTER TABLE
            // These constraints should be enforced at the application level
            
            // Add index for better performance (only if it doesn't exist)
            if (!$this->indexExists('batch_uploads', 'status_created_at_index')) {
                $table->index(['status', 'created_at'], 'status_created_at_index');
            }
            if (!$this->indexExists('batch_uploads', 'user_status_index')) {
                $table->index(['user_id', 'status'], 'user_status_index');
            }
        });

        // Add constraints to users table
        Schema::table('users', function (Blueprint $table) {
            // Note: SQLite doesn't support CHECK constraints in ALTER TABLE
            // These constraints should be enforced at the application level
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
            $table->dropIndex('status_created_at_index');
            $table->dropIndex('batch_status_index');
        });

        // Remove constraints from batch_uploads table
        Schema::table('batch_uploads', function (Blueprint $table) {
            $table->dropIndex('status_created_at_index');
            $table->dropIndex('user_status_index');
        });
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("PRAGMA index_list({$table})");
        foreach ($indexes as $index) {
            if ($index->name === $indexName) {
                return true;
            }
        }
        return false;
    }
};