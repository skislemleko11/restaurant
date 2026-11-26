<?php
declare(strict_types=1);

namespace App\Services\Registration;

use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use PDOException;
use App\Repositories\UserRepositoryInterface;
use Random\RandomException;
use Throwable;

final readonly class RegistrationService
{
    public function __construct(
        private PDO $connection,
        private UserRepositoryInterface $userRepository,
        private EmailVerificationMailer $mailer
    ) {
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function register(string $email, string $password): void
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
            ->modify('+24 hours')
            ->format('Y-m-d H:i:s');

        try {
            $this->connection->beginTransaction();

            $this->userRepository->create(
                $email,
                password_hash($password, PASSWORD_ARGON2ID),
                $token,
                $expiresAt
            );

            $this->mailer->send($email, $token);
            $this->connection->commit();
        } catch (PDOException $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            if ($exception->getCode() === '23000') {
                throw new RegistrationException('Konto z tym adresem email już istnieje.', previous: $exception);
            }

            throw new RegistrationException('Nie udało się utworzyć konta.', previous: $exception);
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw new RegistrationException(
                'Konto nie zostało utworzone, ponieważ nie udało się wysłać maila weryfikacyjnego.',
                previous: $exception
            );
        }
    }
}
