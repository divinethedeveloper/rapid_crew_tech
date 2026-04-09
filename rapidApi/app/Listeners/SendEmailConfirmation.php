<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\orderConfirmation;
use Illuminate\Support\Facades\Mail;

class SendEmailConfirmation
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
        Mail::to($event->order->email)->send(new orderConfirmation($event->order));
        Mail::to('rapidcrewtech@gmail.com')->send(new orderConfirmation($event->order, true));
    }
}
