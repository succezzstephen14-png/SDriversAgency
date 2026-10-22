<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendServiceRequestExpiryReminders extends Command
{
    protected $signature = 'service-requests:send-expiry-reminders';

    protected $description = 'Notify admins and customers before an agreed service request expires';

    public function handle(): int
    {
        $today = now()->startOfDay();
        $requests = ServiceRequest::query()
            ->whereIn('status', ['paid', 'completed'])
            ->whereNotNull('agreement_end_date')
            ->whereIn('expiry_reminder_days', [10, 30])
            ->whereNull('expiry_reminder_sent_at')
            ->whereDate('agreement_end_date', '>', $today)
            ->whereDate('agreement_end_date', '<=', $today->copy()->addDays(30))
            ->with('user')
            ->get();

        foreach ($requests as $request) {
            if ($request->agreement_end_date->gt($today->copy()->addDays((int) $request->expiry_reminder_days))) {
                continue;
            }

            $recipients = DB::transaction(function () use ($request, $today) {
                $lockedRequest = ServiceRequest::query()
                    ->whereKey($request->id)
                    ->lockForUpdate()
                    ->first();

                if (
                    !$lockedRequest
                    || $lockedRequest->expiry_reminder_sent_at
                    || !in_array($lockedRequest->status, ['paid', 'completed'], true)
                    || !$lockedRequest->agreement_end_date
                    || $lockedRequest->agreement_end_date->lte($today)
                    || $lockedRequest->agreement_end_date->gt(
                        $today->copy()->addDays((int) $lockedRequest->expiry_reminder_days)
                    )
                ) {
                    return null;
                }

                $daysRemaining = $today->diffInDays($lockedRequest->agreement_end_date);
                $title = 'Service agreement nearing expiry';
                $message = "Service request #{$lockedRequest->id} is due to end on "
                    . $lockedRequest->agreement_end_date->format('F j, Y')
                    . " ({$daysRemaining} days remaining). Please contact the agency to discuss the next steps.";

                Notification::create([
                    'user_id' => $lockedRequest->user_id,
                    'title' => $title,
                    'message' => $message,
                    'type' => 'service_request_expiry',
                    'status' => 'unread',
                ]);

                $adminEmails = User::where('role', 'admin')
                    ->whereNotNull('email')
                    ->pluck('email')
                    ->all();

                foreach (User::where('role', 'admin')->get(['id']) as $admin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'title' => $title,
                        'message' => $message,
                        'type' => 'service_request_expiry',
                        'status' => 'unread',
                    ]);
                }

                $lockedRequest->update(['expiry_reminder_sent_at' => now()]);

                return [
                    'request_id' => $lockedRequest->id,
                    'title' => $title,
                    'message' => $message,
                    'customer_email' => $lockedRequest->user?->email,
                    'customer_name' => $lockedRequest->user?->name ?? 'Customer',
                    'admin_emails' => array_values(array_unique($adminEmails)),
                ];
            });

            if ($recipients) {
                $this->sendExpiryEmail(
                    $recipients['customer_email'],
                    $recipients['title'],
                    "Hello {$recipients['customer_name']},\n\n{$recipients['message']}"
                );

                foreach ($recipients['admin_emails'] as $email) {
                    $this->sendExpiryEmail(
                        $email,
                        $recipients['title'] . " — Request #{$recipients['request_id']}",
                        $recipients['message']
                    );
                }

                $this->info("Sent expiry reminder for service request #{$recipients['request_id']}.");
            }
        }

        return self::SUCCESS;
    }

    private function sendExpiryEmail(?string $email, string $subject, string $body): void
    {
        if (!$email) {
            return;
        }

        try {
            Mail::raw($body, function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });
        } catch (\Throwable $exception) {
            Log::error('Could not send service agreement expiry email.', [
                'email' => $email,
                'subject' => $subject,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
