<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use App\Models\Tenant;
use App\Models\Domain;
use App\Models\Hostname;
use App\Models\Graph;
use App\Models\FingerPrint;
use App\Models\Configure;

class DiffNotifyMail extends Mailable
{
    use Queueable, SerializesModels;

    // domains must be domain_name => [types => [name => type_id], graphs => [graph_parent_name => [graph_child_name]], fingers => [module_names => finger_print]]
    private $domains;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($domains)
    {
        //
        $this->domains = $domains;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'プログラム変更検出通知',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            text: 'emails.diff_notify_mail',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
