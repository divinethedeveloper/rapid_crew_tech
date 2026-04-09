<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\User;
use Illuminate\Support\Str;

class CreateUserFromOrderDetails
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $check = User::where('email', $event->order->email)->exists();

        if ($check) {
            return;
        }

        User::create([
            'name' => $event->order->name,
            'email' => $event->order->email,
            'phone_number' => $event->order->phone_number,
            'password' => $event->order->password,
            'remember_token' => Str::random(50),
        ]);
    }
}
