<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EmailService
{
    public function send(
    string|array $to,
    string $subject,
    string $html,
    ?string $from = null
): bool {
    try {
        return match (config('mail.provider', 'resend')) {
            'resend' => $this->sendViaResend($to, $subject, $html, $from),
            default => throw new \Exception('Unsupported mail provider'),
        };
    } catch (\Exception $e) {
        \Log::error('Email sending failed: '.$e->getMessage(), [
            'to' => $to,
            'subject' => $subject,
        ]);
        throw $e;
    }
}

    private function sendViaResend(
    string|array $to,
    string $subject,
    string $html,
    ?string $from
): bool {
    $response = Http::withToken(config('services.resend.key'))
        ->post('https://api.resend.com/emails', [
            'from' => $from ?? config('mail.from.address'),
            'to' => (array) $to,
            'subject' => $subject,
            'html' => $html,
        ]);

    if ($response->failed()) {
        $status = $response->status();
        $body = $response->body();
        
        // Log detailed error
        \Log::error("Resend API Error ({$status}): {$body}");

        // You can throw an exception with details
        throw new \Exception("Email sending failed with status {$status}: {$body}");
    }

    return true;
}

}
