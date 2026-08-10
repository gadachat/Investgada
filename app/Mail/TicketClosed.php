<?php

namespace App\Mail;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketClosed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SupportTicket $ticket) {}

    public function build()
    {
        return $this->subject("Ticket Closed: {$this->ticket->ticket_number}")
            ->view('emails.ticket-closed')
            ->with(['ticket' => $this->ticket]);
    }
}
