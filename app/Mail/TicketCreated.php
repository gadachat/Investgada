<?php

namespace App\Mail;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SupportTicket $ticket) {}

    public function build()
    {
        return $this->subject("New Ticket: {$this->ticket->ticket_number} — {$this->ticket->subject}")
            ->view('emails.ticket-created')
            ->with(['ticket' => $this->ticket]);
    }
}
