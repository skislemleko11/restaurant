<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\ReservationRepositoryInterface;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final readonly class BlockedPeriodService
{
    public function __construct(private ReservationRepositoryInterface $reservationRepository)
    {
    }

    /**
     * @return list<array{id: int, start: string, end: string, reason: string|null}>
     * @throws DateMalformedStringException
     */
    public function getCalendar(): array
    {
        $timezone = new DateTimeZone(ReservationService::TIMEZONE);
        $from = new DateTimeImmutable('today', $timezone);
        $to = $from->modify('+1 year');

        return array_map(
            static fn(array $period): array => [
                'id' => (int)$period['id'],
                'start' => (string)$period['start'],
                'end' => (string)$period['end'],
                'reason' => $period['reason'] === null ? null : (string)$period['reason'],
            ],
            $this->reservationRepository->findBlockedPeriods(
                $from->format('Y-m-d H:i:s'),
                $to->format('Y-m-d H:i:s')
            )
        );
    }

    public function create(mixed $startDate, mixed $endDate, mixed $startTime, mixed $endTime, mixed $reason): void
    {
        [$start, $end, $cleanReason] = $this->validatePeriod($startDate, $endDate, $startTime, $endTime, $reason);
        $this->reservationRepository->createBlockedPeriod($start, $end, $cleanReason);
    }

    public function update(
        mixed $rawId,
        mixed $startDate,
        mixed $endDate,
        mixed $startTime,
        mixed $endTime,
        mixed $reason
    ): void
    {
        $id = filter_var($rawId, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            throw new InvalidArgumentException('Wybrana blokada jest nieprawidłowa.');
        }

        [$start, $end, $cleanReason] = $this->validatePeriod($startDate, $endDate, $startTime, $endTime, $reason);
        if (!$this->reservationRepository->updateBlockedPeriod($id, $start, $end, $cleanReason)) {
            throw new InvalidArgumentException('Wybrana blokada nie istnieje.');
        }
    }

    public function delete(mixed $rawId): void
    {
        $id = filter_var($rawId, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1 || !$this->reservationRepository->deleteBlockedPeriod($id)) {
            throw new InvalidArgumentException('Wybrana blokada nie istnieje.');
        }
    }

    /**
     * @return array{0: string, 1: string, 2: string|null}
     */
    private function validatePeriod(
        mixed $rawStartDate,
        mixed $rawEndDate,
        mixed $rawStartTime,
        mixed $rawEndTime,
        mixed $rawReason
    ): array
    {
        $startDate = $this->validateDate($rawStartDate, 'Podaj datę początkową.');
        $endDate = $this->validateDate($rawEndDate, 'Podaj datę końcową.');
        if ($endDate < $startDate) {
            throw new InvalidArgumentException('Data końcowa nie może być wcześniejsza niż początkowa.');
        }

        $startTime = $this->validateTime($rawStartTime);
        $endTime = $this->validateTime($rawEndTime);
        if (($startTime === null) !== ($endTime === null)) {
            throw new InvalidArgumentException('Podaj obie godziny albo pozostaw oba pola puste.');
        }

        $start = $startDate->setTime(0, 0);
        $end = $endDate->setTime(23, 59, 59);
        if ($startTime !== null && $endTime !== null) {
            $start = $startDate->setTime(...array_map('intval', explode(':', $startTime)));
            $end = $endDate->setTime(...array_map('intval', explode(':', $endTime)));
            if ($end <= $start) {
                throw new InvalidArgumentException('Koniec blokady musi być późniejszy niż jej początek.');
            }
        }

        $reason = is_string($rawReason) ? trim($rawReason) : '';
        if (mb_strlen($reason) > 255) {
            throw new InvalidArgumentException('Powód blokady może mieć maksymalnie 255 znaków.');
        }

        return [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'), $reason === '' ? null : $reason];
    }

    private function validateDate(mixed $value, string $message): DateTimeImmutable
    {
        if (!is_string($value) || preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value) !== 1) {
            throw new InvalidArgumentException($message);
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone(ReservationService::TIMEZONE));
        $errors = DateTimeImmutable::getLastErrors();
        if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            throw new InvalidArgumentException($message);
        }

        return $date;
    }

    private function validateTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_string($value) || preg_match('/^\d{2}:\d{2}$/D', $value) !== 1) {
            throw new InvalidArgumentException('Podana godzina blokady jest nieprawidłowa.');
        }
        $time = DateTimeImmutable::createFromFormat('!H:i', $value, new DateTimeZone(ReservationService::TIMEZONE));
        $errors = DateTimeImmutable::getLastErrors();
        if ($time === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            throw new InvalidArgumentException('Podana godzina blokady jest nieprawidłowa.');
        }

        return $value;
    }
}
