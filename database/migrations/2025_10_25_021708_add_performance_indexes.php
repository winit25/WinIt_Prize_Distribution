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
        // Add indexes for batch_uploads table (if they don't exist)
        try {
            Schema::table('batch_uploads', function (Blueprint $table) {
                if (!$this->indexExists('batch_uploads', 'batch_uploads_status_index')) {
                    $table->index('status');
                }
                if (!$this->indexExists('batch_uploads', 'batch_uploads_user_id_index')) {
                    $table->index('user_id');
                }
                if (!$this->indexExists('batch_uploads', 'batch_uploads_created_at_index')) {
                    $table->index('created_at');
                }
                if (!$this->indexExists('batch_uploads', 'batch_uploads_status_created_at_index')) {
                    $table->index(['status', 'created_at']);
                }
            });
        } catch (Exception $e) {
            // Indexes might already exist, continue
        }

        // Add indexes for recipients table (if they don't exist)
        try {
            Schema::table('recipients', function (Blueprint $table) {
                if (!$this->indexExists('recipients', 'recipients_batch_upload_id_index')) {
                    $table->index('batch_upload_id');
                }
                if (!$this->indexExists('recipients', 'recipients_status_index')) {
                    $table->index('status');
                }
                if (!$this->indexExists('recipients', 'recipients_phone_number_index')) {
                    $table->index('phone_number');
                }
                if (!$this->indexExists('recipients', 'recipients_disco_index')) {
                    $table->index('disco');
                }
                if (!$this->indexExists('recipients', 'recipients_batch_upload_id_status_index')) {
                    $table->index(['batch_upload_id', 'status']);
                }
            });
        } catch (Exception $e) {
            // Indexes might already exist, continue
        }

        // Add indexes for transactions table (if they don't exist)
        try {
            Schema::table('transactions', function (Blueprint $table) {
                if (!$this->indexExists('transactions', 'transactions_batch_upload_id_index')) {
                    $table->index('batch_upload_id');
                }
                if (!$this->indexExists('transactions', 'transactions_recipient_id_index')) {
                    $table->index('recipient_id');
                }
                if (!$this->indexExists('transactions', 'transactions_status_index')) {
                    $table->index('status');
                }
                if (!$this->indexExists('transactions', 'transactions_phone_number_index')) {
                    $table->index('phone_number');
                }
                if (!$this->indexExists('transactions', 'transactions_processed_at_index')) {
                    $table->index('processed_at');
                }
                if (!$this->indexExists('transactions', 'transactions_status_processed_at_index')) {
                    $table->index(['status', 'processed_at']);
                }
                if (!$this->indexExists('transactions', 'transactions_batch_upload_id_status_index')) {
                    $table->index(['batch_upload_id', 'status']);
                }
            });
        } catch (Exception $e) {
            // Indexes might already exist, continue
        }
    }

    private function indexExists($table, $indexName)
    {
        $indexes = DB::select("PRAGMA index_list($table)");
        foreach ($indexes as $index) {
            if ($index->name === $indexName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes for batch_uploads table
        Schema::table('batch_uploads', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
        });

        // Remove indexes for recipients table
        Schema::table('recipients', function (Blueprint $table) {
            $table->dropIndex(['batch_upload_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['phone_number']);
            $table->dropIndex(['disco']);
            $table->dropIndex(['batch_upload_id', 'status']);
        });

        // Remove indexes for transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['batch_upload_id']);
            $table->dropIndex(['recipient_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['phone_number']);
            $table->dropIndex(['processed_at']);
            $table->dropIndex(['status', 'processed_at']);
            $table->dropIndex(['batch_upload_id', 'status']);
        });

        // Remove indexes for users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['created_at']);
        });

        // Remove indexes for activity_logs table
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['model_type', 'model_id']);
            $table->dropIndex(['user_id', 'created_at']);
        });
    }
};