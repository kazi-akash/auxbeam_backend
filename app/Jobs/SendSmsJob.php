<?php

namespace App\Jobs;

use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    protected $phone;
    protected $message;
    protected $userId;
    protected $orderId;
    protected $provider;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $phone,
        string $message,
        ?int $userId = null,
        ?int $orderId = null,
        ?string $provider = null
    ) {
        $this->phone = $phone;
        $this->message = $message;
        $this->userId = $userId;
        $this->orderId = $orderId;
        $this->provider = $provider;
    }

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService): void
    {
        $user = $this->userId ? \App\Models\User::find($this->userId) : null;
        $order = $this->orderId ? \App\Models\Order::find($this->orderId) : null;

        $smsService->send(
            $this->phone,
            $this->message,
            $user,
            $order,
            $this->provider
        );
    }
}
