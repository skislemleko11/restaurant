<?php
declare(strict_types=1);

namespace App\Services\Auth;

use App\EnvUtils;
use App\Mailer;
use PHPMailer\PHPMailer\Exception as MailException;

final readonly class PasswordResetMailer implements PasswordResetMailerInterface
{
    public function __construct(private Mailer $mailer)
    {
    }

    /**
     * @throws PasswordResetException
     * @throws MailException
     */
    public function send(string $recipient, string $token): void
    {
        $this->mailer->addAddress($recipient);
        $this->mailer->isHTML();
        $this->mailer->Subject = 'Reset hasła';
        $this->mailer->Body = $this->createMessage($token);
        $this->mailer->AltBody = 'Ustaw nowe hasło, otwierając link: ' . $this->createResetUrl($token);

        try {
            $this->mailer->send();
        } catch (MailException $exception) {
            throw new PasswordResetException('Nie udało się wysłać maila do resetowania hasła.', previous: $exception);
        }
    }

    private function createMessage(string $token): string
    {
        $url = htmlspecialchars($this->createResetUrl($token), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return <<<HTML
            <!DOCTYPE html>
            <html lang="pl">
            <head><meta charset="UTF-8"><title>Reset hasła</title></head>
            <body style="margin:0;padding:0;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background-color:#f9f6f0;color:#333333;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#f9f6f0;padding:40px 20px;">
                    <tr><td align="center">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width:600px;background-color:#ffffff;border-radius:8px;overflow:hidden;">
                            <tr><td align="center" style="background-color:#e67e22;padding:25px;">
                                <h2 style="margin:0;color:#ffffff;font-size:24px;">Smaki Miasta</h2>
                            </td></tr>
                            <tr><td align="center" style="padding:40px 30px;">
                                <h1 style="margin:0 0 20px;color:#2c3e50;font-size:22px;">Reset hasła</h1>
                                <p style="margin:0 0 30px;color:#555555;font-size:16px;line-height:1.5;">
                                    Otrzymaliśmy prośbę o ustawienie nowego hasła do Twojego konta.
                                </p>
                                <a href="$url" style="background-color:#e67e22;color:#ffffff;text-decoration:none;padding:14px 25px;border-radius:5px;font-weight:bold;font-size:16px;display:inline-block;">
                                    Ustaw nowe hasło
                                </a>
                                <p style="margin:30px 0 0;color:#a4b0be;font-size:13px;">
                                    Link jest ważny przez godzinę. Jeśli to nie Ty wysłałeś tę prośbę, zignoruj tę wiadomość.
                                </p>
                            </td></tr>
                            <tr><td align="center" style="background-color:#2c3e50;padding:20px;">
                                <p style="margin:0;color:#d1d8e0;font-size:12px;">Wiadomość wygenerowana automatycznie.<br>Copyright © 2026 | Smaki Miasta</p>
                            </td></tr>
                        </table>
                    </td></tr>
                </table>
            </body>
            </html>
        HTML;
    }

    private function createResetUrl(string $token): string
    {
        return rtrim(EnvUtils::getRequiredEnvValue('APP_URL'), '/') . '/reset-password/' . urlencode($token);
    }
}
