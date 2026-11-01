<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends BaseVerifyEmail
{
    protected function verificationUrl($notifiable)
    {
        // ✅ Générer un nouveau token à chaque envoi
        $token = $notifiable->generateEmailVerificationToken();

        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
                'token' => $token,   // ← AJOUT
            ]
        );
    }

    public function toMail($notifiable)
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Confirmez votre adresse email — Douceur')
            ->greeting("Bonjour {$notifiable->name},")
            ->line('Merci pour votre inscription chez Douceur 🧁')
            ->line('Pour accéder à votre compte, merci de confirmer votre adresse email.')
            ->action('Confirmer mon email', $url)
            ->line('Ce lien est valable 60 minutes et ne peut être utilisé qu\'une seule fois.')
            ->line("Si vous n'êtes pas à l'origine de cette inscription, ignorez cet email.")
            ->salutation("L'équipe Douceur");
    }
}