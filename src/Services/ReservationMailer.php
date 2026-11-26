<?php
declare(strict_types=1);

namespace App\Services;

use App\EnvUtils;
use App\Mailer;
use PHPMailer\PHPMailer\Exception as MailException;

final readonly class ReservationMailer
{
    public function __construct(private Mailer $mailer)
    {
    }

    /**
     * @throws MailException
     */
    public function send(string $recipient, string $date, string $time, string $tableName, int $people): void
    {
        $reservationsUrl = rtrim(EnvUtils::getRequiredEnvValue('APP_URL'), '/') . '/my-reservations';
        $safeUrl = htmlspecialchars($reservationsUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeDate = htmlspecialchars($date, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeTime = htmlspecialchars($time, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeTable = htmlspecialchars($tableName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $this->mailer->addAddress($recipient);
        $this->mailer->isHTML();
        $this->mailer->Subject = 'Potwierdzenie rezerwacji stolika';
        $this->mailer->Body = <<<HTML
            <!DOCTYPE html>
            <html lang="pl">
            <head><meta charset="UTF-8"><title>Potwierdzenie rezerwacji</title></head>
            <body style="margin:0;padding:40px 20px;background:#f9f6f0;font-family:'Segoe UI',Tahoma,sans-serif;color:#333;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr><td align="center">
                        <table width="600" border="0" cellspacing="0" cellpadding="0" style="max-width:600px;background:#fff;border-radius:8px;">
                            <tr><td align="center" style="background:#e67e22;padding:25px;"><h2 style="margin:0;color:#fff;">Smaki Miasta</h2></td></tr>
                            <tr><td style="padding:40px 30px;">
                                <h1 style="margin:0 0 20px;color:#2c3e50;font-size:22px;">Rezerwacja została potwierdzona</h1>
                                <p><strong>Data:</strong> {$safeDate}</p>
                                <p><strong>Godzina:</strong> {$safeTime}</p>
                                <p><strong>Stolik:</strong> {$safeTable}</p>
                                <p><strong>Liczba osób:</strong> {$people}</p>
                                <p style="margin:30px 0;text-align:center;">
                                    <a href="{$safeUrl}" style="background:#e67e22;color:#fff;text-decoration:none;padding:14px 25px;border-radius:5px;font-weight:bold;">Zobacz moje rezerwacje</a>
                                </p>
                            </td></tr>
                            <tr><td align="center" style="background:#2c3e50;padding:20px;color:#d1d8e0;font-size:12px;">Wiadomość wygenerowana automatycznie.</td></tr>
                        </table>
                    </td></tr>
                </table>
            </body>
            </html>
        HTML;
        $this->mailer->AltBody = "Rezerwacja potwierdzona.\nData: {$date}\nGodzina: {$time}\nStolik: {$tableName}\nLiczba osób: {$people}\nZobacz rezerwacje: {$reservationsUrl}";
        $this->mailer->send();
    }
}
