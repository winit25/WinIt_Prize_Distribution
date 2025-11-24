<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Add comprehensive indexes, constraints, and fields for atomic transactions
     */
    public function up(): void
    {
        // Add transaction_hash for idempotency and preventing duplicates
        if (!Schema::hasColumn('transactions', 'transaction_hash')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('transaction_hash')->unique()->nullable()->after('buypower_reference');
            });
        }

        // Add retry_count for tracking retry attempts
        if (!Schema::hasColumn('transactions', 'retry_count')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->integer('retry_count')->default(0)->after('error_message');
            });
        }

        // Add last_retry_at for tracking retry timing
        if (!Schema::hasColumn('transactions', 'last_retry_at')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->timestamp('last_retry_at')->nullable()->after('processed_at');
            });
        }

        // Add processing started timestamp for timeout detection
        if (!Schema::hasColumn('recipients', 'processing_started_at')) {
            Schema::table('recipients', function (Blueprint $table) {
                $table->timestamp('processing_started_at')->nullable()->after('processed_at');
            });
        }

        // Add indexes for better query performance
        Schema::table('batch_uploads', function (Blueprint $table) {
            // Composite indexes for common queries
            if (!$this->indexExists('batch_uploads', 'idx_status_created')) {
                $table->index(['status', 'created_at'], 'idx_status_created');
            }
            if (!$this->indexExists('batch_uploads', 'idx_user_status')) {
                $table->index(['user_id', 'status'], 'idx_user_status');
            }
        });

        Schema::table('recipients', function (Blueprint $table) {
            // Composite indexes for filtering and status checks
            if (!$this->indexExists('recipients', 'idx_batch_status')) {
                $table->index(['batch_upload_id', 'status'], 'idx_batch_status');
            }
            if (!$this->indexExists('recipients', 'idx_phone_batch')) {
                $table->unique(['batch_upload_id', 'phone_number'], 'idx_phone_batch');
            }
            if (!$this->indexExists('recipients', 'idx_processing_status')) {
                $table->index(['status', 'processing_started_at'], 'idx_processing_status');
            }
        });

        Schema::table('transactions', function (Blueprint $table) {
            // Composite indexes for query optimization
            if (!$this->indexExists('transactions', 'idx_status_processed')) {
                $table->index(['status', 'processed_at'], 'idx_status_processed');
            }
            if (!$this->indexExists('transactions', 'idx_batch_status')) {
                $table->index(['batch_upload_id', 'status'], 'idx_batch_status');
            }
            if (!$this->indexExists('transactions', 'idx_recipient_status')) {
                $table->index(['recipient_id', 'status'], 'idx_recipient_status');
            }
            if (!$this->indexExists('transactions', 'idx_hash')) {
                $table->index('transaction_hash', 'idx_hash');
            }
            if (!$this->indexExists('transactions', 'idx_retry_count')) {
                $table->index('retry_count', 'idx_retry_count');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            // Indexes for user lookups
            if (!$this->indexExists('users', 'idx_email')) {
                $table->index('email', 'idx_email');
            }
            if (!$this->indexExists('users', 'idx_created')) {
                $table->index('created_at', 'idx_created');
            }
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            // Indexes for audit trail queries
            if (!$this->indexExists('activity_logs', 'idx_causer')) {
                $table->index('causer_id', 'idx_causer');
            }
            if (!$this->indexExists('activity_logs', 'idx_event_created')) {
                $table->index(['event', 'created_at'], 'idx_event_created');
            }
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['transaction_hash', 'retry_count', 'last_retry_at']);
        });

        Schema::table('recipients', function (Blueprint $table) {
            $table->dropColumn('processing_started_at');
        });

        // Drop indexes
        $this->dropIndexIfExists('batch_uploads', 'idx_status_created');
        $this->dropIndexIfExists('batch_uploads', 'idx_user_status');
        $this->dropIndexIfExists('recipients', 'idx_batch_status');
        $this->dropIndexIfExists('recipients', 'idx_phone_batch');
        $this->dropIndexIfExists('recipients', 'idx_processing_status');
        $this->dropIndexIfExists('transactions', 'idx_status_processed');
        $this->dropIndexIfExists('transactions', 'idx_batch_status');
        $this->dropIndexIfExists('transactions', 'idx_recipient_status');
        $this->dropIndexIfExists('transactions', 'idx_hash');
        $this->dropIndexIfExists('transactions', 'idx_retry_count');
        $this->dropIndexIfExists('users', 'idx_email');
        $this->dropIndexIfExists('users', 'idx_created');
        $this->dropIndexIfExists('activity_logs', 'idx_causer');
        $this->dropIndexIfExists('activity_logs', 'idx_event_created');
    }

    /**
     * Check if an index exists
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $indexes = DB::select(
                "SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS 
                WHERE TABLE_NAME = ? AND INDEX_NAME = ?",
                [DB::getTablePrefix() . $table, $indexName]
            );
            return count($indexes) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Drop index if it exists
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        try {
            if ($this->indexExists($table, $indexName)) {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
            }
        } catch (\Exception $e) {
            // Silently ignore if index doesn't exist
        }
    }
};
