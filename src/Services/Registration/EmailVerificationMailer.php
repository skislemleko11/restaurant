<?php
declare(strict_types=1);

namespace App\Services\Registration;

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

final class EmailVerificationMailer
{
    public function send(string $recipient, string $token): void
    {
        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = $this->requiredEnvironmentValue('MAIL_HOST');
        $mailer->SMTPAuth = true;
        $mailer->Username = $this->requiredEnvironmentValue('MAIL_USERNAME');
        $mailer->Password = $this->requiredEnvironmentValue('MAIL_PASSWORD');
        $mailer->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?? PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port = (int)($_ENV['MAIL_PORT'] ?? 587);
        $mailer->CharSet = 'UTF-8';
        $mailer->setFrom(
            $this->requiredEnvironmentValue('MAIL_FROM_ADDRESS'),
            $_ENV['MAIL_FROM_NAME'] ?? 'Restauracja'
        );
        $mailer->addAddress($recipient);
        $mailer->isHTML();
        $mailer->Subject = 'Potwierdź adres email';
        $mailer->Body = $this->createMessage($token);
        $mailer->AltBody = 'Potwierdź adres email, otwierając link: ' . $this->createVerificationUrl($token);

        try {
            $mailer->send();
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
        return rtrim($this->requiredEnvironmentValue('APP_URL'), '/') . '/verify-email?token=' . urlencode($token);
    }

    private function requiredEnvironmentValue(string $key): string
    {
        $value = trim((string)($_ENV[$key] ?? ''));

        if ($value === '') {
            throw new RegistrationException('Brak konfiguracji wysyłki maili: ' . $key . '.');
        }

        return $value;
    }
}
