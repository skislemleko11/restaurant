<?php
declare(strict_types=1);

namespace App\Services\Auth;

use App\Repositories\UserRepositoryInterface;
use App\Repositories\PasswordResetRepositoryInterface;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use Random\RandomException;
use Throwable;

final readonly class PasswordResetService
{
    public function __construct(
        private PDO $connection,
        private UserRepositoryInterface $userRepository,
        private PasswordResetRepositoryInterface $passwordResetRepository,
        private PasswordResetMailerInterface $mailer
    ) {
    }

    /**
     * @param string $email
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function requestReset(string $email): void
    {
        $accountId = $this->userRepository->findVerifiedAccountIdByEmail($email);
        if ($accountId === null) {
            return;
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
            ->modify('+1 hour')
            ->format('Y-m-d H:i:s');

        try {
            $this->connection->beginTransaction();
            $this->passwordResetRepository->save($accountId, $token, $expiresAt);
            $this->mailer->send($email, $token);
            $this->connection->commit();
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            if ($exception instanceof PasswordResetException) {
                throw $exception;
            }

            throw new PasswordResetException('Nie udało się przygotować resetu hasła.', previous: $exception);
        }
    }

    /**
     * @throws PasswordResetException
     */
    public function reset(string $token, string $password): void
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            throw new PasswordResetException('Link do resetowania hasła jest nieprawidłowy lub wygasł.');
        }

        try {
            $this->connection->beginTransaction();
            $accountId = $this->passwordResetRepository->findAccountIdByValidToken($token);

            if ($accountId === null) {
                $this->connection->rollBack();
                throw new PasswordResetException('Link do resetowania hasła jest nieprawidłowy lub wygasł.');
            }

            $this->userRepository->updatePassword(
                $accountId,
                password_hash($password, PASSWORD_ARGON2ID)
            );
            $this->passwordResetRepository->deleteByAccountId($accountId);
            $this->connection->commit();
        } catch (PasswordResetException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw new PasswordResetException('Nie udało się ustawić nowego hasła.', previous: $exception);
        }
    }
}
