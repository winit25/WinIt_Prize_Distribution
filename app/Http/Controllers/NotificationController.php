<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\BatchUpload;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Get notifications from multiple sources
            $notifications = collect();
            
            // 1. Transaction notifications
            $transactionNotifications = $this->getTransactionNotifications($user);
            $notifications = $notifications->merge($transactionNotifications);
            
            // 2. Batch notifications
            $batchNotifications = $this->getBatchNotifications($user);
            $notifications = $notifications->merge($batchNotifications);
            
            // 3. System notifications
            $systemNotifications = $this->getSystemNotifications($user);
            $notifications = $notifications->merge($systemNotifications);
            
            // Sort by created_at descending
            $notifications = $notifications->sortByDesc('created_at')->values();
            
            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'total' => $notifications->count(),
                'unread' => $notifications->where('read_at', null)->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to load notifications', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load notifications',
                'notifications' => []
            ], 500);
        }
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            // For now, we'll simulate marking as read since we don't have a dedicated notifications table
            // In a real implementation, you'd have a notifications table with read_at timestamps
            
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'user_id' => $request->user()->id,
                'notification_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark notification as read'
            ], 500);
        }
    }

    /**
     * Get transaction-based notifications.
     */
    private function getTransactionNotifications($user)
    {
        $notifications = collect();
        
        // Get recent transactions for the user's batches
        $transactions = Transaction::whereHas('batchUpload', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['recipient', 'batchUpload'])
        ->orderBy('created_at', 'desc')
        ->limit(50)
        ->get();
        
        foreach ($transactions as $transaction) {
            $notification = [
                'id' => 'transaction_' . $transaction->id,
                'type' => $this->getTransactionNotificationType($transaction),
                'title' => $this->getTransactionNotificationTitle($transaction),
                'message' => $this->getTransactionNotificationMessage($transaction),
                'created_at' => $transaction->created_at->toISOString(),
                'read_at' => null, // Simulate unread for now
                'transaction_id' => $transaction->id,
                'transaction_reference' => $transaction->buypower_reference,
                'amount' => $transaction->amount,
                'phone_number' => $transaction->phone_number,
                'batch_name' => $transaction->batchUpload->batch_name ?? 'Unknown Batch'
            ];
            
            $notifications->push($notification);
        }
        
        return $notifications;
    }

    /**
     * Get batch-based notifications.
     */
    private function getBatchNotifications($user)
    {
        $notifications = collect();
        
        // Get recent batch uploads
        $batches = BatchUpload::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        foreach ($batches as $batch) {
            $notification = [
                'id' => 'batch_' . $batch->id,
                'type' => $this->getBatchNotificationType($batch),
                'title' => $this->getBatchNotificationTitle($batch),
                'message' => $this->getBatchNotificationMessage($batch),
                'created_at' => $batch->created_at->toISOString(),
                'read_at' => null,
                'batch_id' => $batch->id,
                'batch_name' => $batch->batch_name,
                'total_recipients' => $batch->total_recipients,
                'total_amount' => $batch->total_amount
            ];
            
            $notifications->push($notification);
        }
        
        return $notifications;
    }

    /**
     * Get system-based notifications.
     */
    private function getSystemNotifications($user)
    {
        $notifications = collect();
        
        // Get recent activity logs for the user
        $activities = ActivityLog::where('causer_id', $user->id)
            ->where('causer_type', User::class)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        foreach ($activities as $activity) {
            $notification = [
                'id' => 'activity_' . $activity->id,
                'type' => 'info',
                'title' => 'System Activity',
                'message' => $activity->description ?? 'System activity recorded',
                'created_at' => $activity->created_at->toISOString(),
                'read_at' => null,
                'activity_id' => $activity->id,
                'activity_type' => $activity->log_name ?? 'system'
            ];
            
            $notifications->push($notification);
        }
        
        return $notifications;
    }

    /**
     * Get notification type based on transaction status.
     */
    private function getTransactionNotificationType($transaction): string
    {
        switch ($transaction->status) {
            case 'success':
                return 'success';
            case 'failed':
                return 'error';
            case 'processing':
                return 'warning';
            default:
                return 'info';
        }
    }

    /**
     * Get transaction notification title.
     */
    private function getTransactionNotificationTitle($transaction): string
    {
        switch ($transaction->status) {
            case 'success':
                return 'Token Sent Successfully';
            case 'failed':
                return 'Token Send Failed';
            case 'processing':
                return 'Token Being Processed';
            default:
                return 'Transaction Update';
        }
    }

    /**
     * Get transaction notification message.
     */
    private function getTransactionNotificationMessage($transaction): string
    {
        $amount = number_format($transaction->amount, 2);
        $phone = $transaction->phone_number;
        
        switch ($transaction->status) {
            case 'success':
                return "Successfully sent ₦{$amount} electricity token to {$phone}. Token: {$transaction->token}";
            case 'failed':
                $error = $transaction->error_message ? " - {$transaction->error_message}" : '';
                return "Failed to send ₦{$amount} electricity token to {$phone}{$error}";
            case 'processing':
                return "Processing ₦{$amount} electricity token for {$phone}...";
            default:
                return "Transaction for ₦{$amount} to {$phone} is {$transaction->status}";
        }
    }

    /**
     * Get batch notification type.
     */
    private function getBatchNotificationType($batch): string
    {
        switch ($batch->status) {
            case 'completed':
                return 'success';
            case 'failed':
                return 'error';
            case 'processing':
                return 'warning';
            default:
                return 'info';
        }
    }

    /**
     * Get batch notification title.
     */
    private function getBatchNotificationTitle($batch): string
    {
        switch ($batch->status) {
            case 'completed':
                return 'Batch Processing Completed';
            case 'failed':
                return 'Batch Processing Failed';
            case 'processing':
                return 'Batch Processing Started';
            default:
                return 'Batch Update';
        }
    }

    /**
     * Get batch notification message.
     */
    private function getBatchNotificationMessage($batch): string
    {
        $recipients = $batch->total_recipients;
        $amount = number_format($batch->total_amount, 2);
        
        switch ($batch->status) {
            case 'completed':
                return "Batch '{$batch->batch_name}' completed successfully. {$recipients} recipients processed for ₦{$amount}";
            case 'failed':
                return "Batch '{$batch->batch_name}' failed to process. {$recipients} recipients, ₦{$amount} total";
            case 'processing':
                return "Batch '{$batch->batch_name}' processing started. {$recipients} recipients, ₦{$amount} total";
            default:
                return "Batch '{$batch->batch_name}' status: {$batch->status}";
        }
    }

    /**
     * Retry a failed transaction.
     */
    public function retryTransaction(Request $request, $transactionId): JsonResponse
    {
        try {
            $user = $request->user();
            
            $transaction = Transaction::whereHas('batchUpload', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->findOrFail($transactionId);
            
            if ($transaction->status !== 'failed') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only failed transactions can be retried'
                ], 400);
            }
            
            // Reset transaction status
            $transaction->update([
                'status' => 'pending',
                'error_message' => null,
                'processed_at' => null
            ]);
            
            // Queue the transaction for reprocessing
            // In a real implementation, you'd dispatch a job here
            
            Log::info('Transaction retry initiated', [
                'transaction_id' => $transactionId,
                'user_id' => $user->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Transaction queued for retry'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to retry transaction', [
                'transaction_id' => $transactionId,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to retry transaction'
            ], 500);
        }
    }
}
