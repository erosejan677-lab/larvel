<?php

namespace App\Console\Commands;

use App\Mail\AskForReviewEmail;
use App\Models\Order;
use App\Models\ReviewLoginToken;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SendReviewRequests extends Command
{
    protected $signature = 'reviews:send-last-24h';
    protected $description = 'Create users for guest orders in last 24h and email magic review links';

    public function handle(): int
    {
        // Optional: add a nullable column 'review_asked_at' to orders to avoid re-sending
        // Schema::table('orders', fn($t) => $t->timestamp('review_asked_at')->nullable());

        $orders = Order::query()
            ->where('asked_for_review', 0)
            ->with(['buyer:id,email,first_name'])   // eager-load buyer to avoid N+1
            ->get();


        $this->info("Found {$orders->count()} eligible orders.");

        foreach ($orders as $order) {
            DB::transaction(function () use ($order) {
                // 1) Pick email: guest first, fallback to buyer email
                $email = strtolower(trim((string)($order->guest_email ?? '')));
                if (!$email) {
                    $email = strtolower(trim((string)($order->buyer?->email ?? '')));
                }
                if (!$email) {
                    \Log::warning('Skipping review email: no guest_email or buyer email', ['order_id' => $order->id]);
                    return;
                }

                // 2) Validate email (syntax + MX)
                $v = Validator::make(['email' => $email], ['email' => 'required|email:filter,dns']);
                if ($v->fails()) {
                    \Log::warning('Skipping review email: invalid email', [
                        'order_id' => $order->id,
                        'email'    => $email,
                        'errors'   => $v->errors()->all(),
                    ]);
                    return;
                }


                // 3) Find/Create user (guest will be created; existing buyer stays unchanged)
                $displayName = $order->guest_name
                    ?? $order->buyer?->first_name
                    ?? $order->buyer?->name
                    ?? Str::before($email, '@');

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'first_name' => $displayName,
                        'password'   => Hash::make('12345678'), // never plain text
                    ]
                );

                // 2) Create single-use token (valid 48h)
                $token = Str::random(64);

                ReviewLoginToken::create([
                    'user_id' => $user->id,
                    'token' => $token,   // ⚠️ plain storage
                    'expires_at' => now()->addHours(48),
                ]);

                $base = config('services.magic_link');
                $magicLink = "{$base}/api/v1/auth/review-login/redirect/{$token}?redirect=/user/{$order->seller_id}";

                Mail::to($email)->queue(new AskForReviewEmail($magicLink, $user->name ?? 'there'));


                // 5) mark order so we don’t re-send
                $order->update(['asked_for_review' => 1]);
            });
        }

        return self::SUCCESS;
    }
}
