<?php

namespace App\Mail;

use App\Models\User;
use App\Modules\SuperAdmin\Domain\Models\School;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SchoolAdminCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public School $school,
        public User $user,
        public string $plainPassword,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vos identifiants de connexion AcademiaERP',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.school-admin-credentials',
        );
    }
}
