<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class ActivityLoggingService
{
    public function log(string $action, string $description, ?Model $model = null, array $properties = []): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function logBatchCreated($batch): ActivityLog
    {
        return $this->log(
            'batch_created',
            "Created batch: {$batch->batch_name}",
            $batch,
            [
                'batch_name' => $batch->batch_name,
                'total_recipients' => $batch->total_recipients,
                'total_amount' => $batch->total_amount,
            ]
        );
    }

    public function logBatchStatusChanged($batch, string $oldStatus, string $newStatus): ActivityLog
    {
        return $this->log(
            'batch_status_changed',
            "Batch status changed from {$oldStatus} to {$newStatus}",
            $batch,
            [
                'batch_name' => $batch->batch_name,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]
        );
    }

    public function logTransactionCreated($transaction): ActivityLog
    {
        return $this->log(
            'transaction_created',
            "Transaction created for {$transaction->recipient->name ?? 'Unknown'}",
            $transaction,
            [
                'recipient_name' => $transaction->recipient->name ?? 'Unknown',
                'phone_number' => $transaction->phone_number,
                'amount' => $transaction->amount,
                'status' => $transaction->status,
            ]
        );
    }

    public function logTransactionStatusChanged($transaction, string $oldStatus, string $newStatus): ActivityLog
    {
        return $this->log(
            'transaction_status_changed',
            "Transaction status changed from {$oldStatus} to {$newStatus}",
            $transaction,
            [
                'recipient_name' => $transaction->recipient->name ?? 'Unknown',
                'phone_number' => $transaction->phone_number,
                'amount' => $transaction->amount,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]
        );
    }

    public function logTokenGenerated($transaction): ActivityLog
    {
        return $this->log(
            'token_generated',
            "Token generated for transaction",
            $transaction,
            [
                'recipient_name' => $transaction->recipient->name ?? 'Unknown',
                'phone_number' => $transaction->phone_number,
                'amount' => $transaction->amount,
                'token' => $transaction->token,
            ]
        );
    }

    public function logUserCreated($user): ActivityLog
    {
        return $this->log(
            'user_created',
            "User created: {$user->name} ({$user->email})",
            $user,
            [
                'name' => $user->name,
                'email' => $user->email,
                'created_by' => Auth::id(),
            ]
        );
    }

    public function logUserLogin($user): ActivityLog
    {
        return $this->log(
            'user_login',
            "User logged in: {$user->name}",
            $user,
            [
                'name' => $user->name,
                'email' => $user->email,
                'login_time' => now()->toISOString(),
            ]
        );
    }

    public function logUserLogout($user): ActivityLog
    {
        return $this->log(
            'user_logout',
            "User logged out: {$user->name}",
            $user,
            [
                'name' => $user->name,
                'email' => $user->email,
                'logout_time' => now()->toISOString(),
            ]
        );
    }

    public function logPasswordChanged($user): ActivityLog
    {
        return $this->log(
            'password_changed',
            "Password changed for user: {$user->name}",
            $user,
            [
                'name' => $user->name,
                'email' => $user->email,
                'changed_by' => Auth::id(),
            ]
        );
    }

    public function logApiCall(string $endpoint, array $requestData, array $responseData, bool $success = true): ActivityLog
    {
        return $this->log(
            'api_call',
            "API call to {$endpoint} - " . ($success ? 'Success' : 'Failed'),
            null,
            [
                'endpoint' => $endpoint,
                'request_data' => $requestData,
                'response_data' => $responseData,
                'success' => $success,
            ]
        );
    }

    public function logError(string $error, ?Model $model = null, array $context = []): ActivityLog
    {
        return $this->log(
            'error',
            "Error occurred: {$error}",
            $model,
            array_merge($context, [
                'error_message' => $error,
                'error_time' => now()->toISOString(),
            ])
        );
    }

    public function getActivityLogs(int $limit = 50, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = ActivityLog::with('user')->latest();

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['model_type'])) {
            $query->where('model_type', $filters['model_type']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate($limit);
    }
}
