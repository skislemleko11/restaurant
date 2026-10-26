<?php
declare(strict_types=1);

namespace App;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * @mixin PHPMailer
 */
final readonly class Mailer
{
    private PHPMailer $mailer;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host = EnvUtils::getRequiredEnvValue('MAIL_HOST');
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = EnvUtils::getRequiredEnvValue('MAIL_USERNAME');
        $this->mailer->Password = EnvUtils::getRequiredEnvValue('MAIL_PASSWORD');
        $this->mailer->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?? PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = (int)($_ENV['MAIL_PORT'] ?? 587);
        $this->mailer->CharSet = 'UTF-8';
        $this->mailer->setFrom(
            EnvUtils::getRequiredEnvValue('MAIL_FROM_ADDRESS'),
            $_ENV['MAIL_FROM_NAME'] ?? 'Restauracja'
        );
    }

    public function __set(string $name, mixed $value): void
    {
        $this->mailer->$name = $value;
    }

    public function __get(string $name): mixed
    {
        return $this->mailer->$name;
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->mailer->$name(...$arguments);
    }
}