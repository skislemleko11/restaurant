<?php
declare(strict_types=1);

namespace App\Services\Registration;

use App\EnvUtils;
use App\Mailer;
use PHPMailer\PHPMailer\Exception as MailException;

final readonly class EmailVerificationMailer
{
    public function __construct(private Mailer $mailer)
    {
    }

    /**
     * @throws MailException
     */
    public function send(string $recipient, string $token): void
    {
        $this->mailer->addAddress($recipient);
        $this->mailer->isHTML();
        $this->mailer->Subject = 'Potwierdź adres email';
        $this->mailer->Body = $this->createMessage($token);
        $this->mailer->AltBody = 'Potwierdź adres email, otwierając link: ' . $this->createVerificationUrl($token);

        try {
            $this->mailer->send();
        } catch (MailException $exception) {
            throw new RegistrationException('Nie udało się wysłać maila weryfikacyjnego.', previous: $exception);
        }
    }

    private function createMessage(string $token): string
    {
        $url = htmlspecialchars($this->createVerificationUrl($token), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<p>Dziękujemy za rejestrację.</p>'
            . '<p><a href="' . $url . '">Kliknij tutaj, aby potwierdzić adres email</a>.</p>'
            . '<p>Link jest ważny przez 24 godziny.</p>';
    }

    private function createVerificationUrl(string $token): string
    {
        return rtrim(EnvUtils::getRequiredEnvValue('APP_URL'), '/') . '/verify-email/' . urlencode($token);
    }
}
