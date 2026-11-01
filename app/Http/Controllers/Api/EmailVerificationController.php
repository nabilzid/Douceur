<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    /**
     * Valide le lien de vérification envoyé par mail.
     * Redirige vers /verifier-email?status=success.
     */
    public function verify(Request $request, int $id, string $hash)
    {
        $user = User::findOrFail($id);

        // 1. Vérifier le hash (email)
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect('/verifier-email?status=invalid');
        }

        // 2. Vérifier le token (usage unique)
        $plainToken = $request->query('token');

        if (! $plainToken || ! $user->email_verification_token) {
            return redirect('/verifier-email?status=invalid');
        }

        $expectedHash = hash('sha256', $plainToken);

        if (! hash_equals((string) $user->email_verification_token, $expectedHash)) {
            return redirect('/verifier-email?status=invalid');
        }

        // 3. Vérifier que le token n'est pas trop vieux (60 min max)
        if (
            $user->email_verification_sent_at &&
            $user->email_verification_sent_at->lt(now()->subMinutes(60))
        ) {
            return redirect('/verifier-email?status=expired');
        }

        // 4. Si déjà vérifié → already-verified
        if ($user->hasVerifiedEmail()) {
            return redirect('/verifier-email?status=already-verified');
        }

        // 5. Marquer comme vérifié + invalider le token
        if ($user->markEmailAsVerified()) {
            $user->forceFill([
                'email_verification_token' => null,
                'email_verification_sent_at' => null,
            ])->save();

            event(new Verified($user));
        }

        return redirect('/verifier-email?status=success');
    }

    /**
     * Renvoie le mail de vérification (auth requise, mais PAS verified).
     */
    public function resend(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Votre email est déjà vérifié.',
            ], 422);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Un nouvel email de vérification vous a été envoyé.',
        ]);
    }
}