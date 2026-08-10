<?php

namespace App\Mail;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketReplied extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SupportTicket $ticket,
        public SupportTicketMessage $message,
        public bool $isAdminReply = false
    ) {}

    public function build()
    {
        $subject = $this->isAdminReply
            ? "Support Reply: {$this->ticket->ticket_number} — {$this->ticket->subject}"
            : "Ticket Update: {$this->ticket->ticket_number} — {$this->ticket->subject}";

        return $this->subject($subject)
            ->view('emails.ticket-replied')
            ->with([
                'ticket'  => $this->ticket,
                'message' => $this->message,
                'isAdminReply' => $this->isAdminReply,
            ]);
    }
}
