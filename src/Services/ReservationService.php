<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\ReservationRepositoryInterface;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Throwable;

final readonly class ReservationService
{
    public const string TIMEZONE = 'Europe/Warsaw';
    public const string OPENING_TIME = '12:00';
    public const string CLOSING_TIME = '22:00';
    public const int SLOT_INTERVAL_MINUTES = 30;
    public const int MINIMUM_ADVANCE_MINUTES = 60;
    public const int MAX_DAYS_AHEAD = 30;
    public const int MAX_PARTY_SIZE = 12;
    public const int DEV_ACCOUNT_ID = 1;

    public function __construct(private ReservationRepositoryInterface $reservationRepository)
    {
    }

    public function getReservationDurationMinutes(): int
    {
        return 90;
    }

    /**
     * @return list<array{time: string, available_tables: int}>
     * @throws DateMalformedStringException
     */
    public function getSlots(mixed $rawDate, mixed $rawPeople): array
    {
        $date = $this->validateDate($rawDate);
        $people = $this->validatePeople($rawPeople);
        $opening = $this->timeOnDate($date, self::OPENING_TIME);
        $closing = $this->timeOnDate($date, self::CLOSING_TIME);
        $lastStart = $closing->modify('-' . $this->getReservationDurationMinutes() . ' minutes');
        $minimumStart = new DateTimeImmutable('now', $this->timezone())
            ->modify('+' . self::MINIMUM_ADVANCE_MINUTES . ' minutes');

        $slots = [];
        for ($slot = $opening; $slot <= $lastStart; $slot = $slot->modify('+' . self::SLOT_INTERVAL_MINUTES . ' minutes')) {
            if ($slot < $minimumStart) {
                continue;
            }

            [$windowStart, $windowEnd] = $this->collisionWindow($slot);
            $availableTables = $this->reservationRepository->countAvailableTables(
                $people,
                $windowStart,
                $windowEnd
            );

            $slots[] = [
                'time' => $slot->format('H:i'),
                'available_tables' => $availableTables
            ];
        }

        return $slots;
    }

    /**
     * @return list<array{table_id: int, location: string, capacity: int, is_reserved: bool, fits_party: bool, is_available: bool}>
     */
    public function getTables(mixed $rawDate, mixed $rawTime, mixed $rawPeople): array
    {
        $people = $this->validatePeople($rawPeople);
        $slot = $this->validateSlot($rawDate, $rawTime);
        [$windowStart, $windowEnd] = $this->collisionWindow($slot);

        $tables = [];
        foreach ($this->reservationRepository->findTables($windowStart, $windowEnd) as $table) {
            $capacity = (int)$table['capacity'];
            $isReserved = (bool)$table['is_reserved'];
            $fitsParty = $capacity >= $people;

            $tables[] = [
                'table_id' => (int)$table['table_id'],
                'location' => (string)$table['location'],
                'capacity' => $capacity,
                'is_reserved' => $isReserved,
                'fits_party' => $fitsParty,
                'is_available' => !$isReserved && $fitsParty
            ];
        }

        return $tables;
    }

    /**
     * @throws DateMalformedStringException
     * @throws Throwable
     */
    public function createReservation(
        int $accountId,
        mixed $rawDate,
        mixed $rawTime,
        mixed $rawTableId,
        mixed $rawPeople
    ): void {
        $people = $this->validatePeople($rawPeople);
        $slot = $this->validateSlot($rawDate, $rawTime);
        $tableId = $this->validatePositiveInteger($rawTableId, 'Wybierz stolik.');

        $this->reservationRepository->beginTransaction();

        try {
            $capacity = $this->reservationRepository->findCapacityForUpdate($tableId);

            if ($capacity === null) {
                throw new InvalidArgumentException('Wybrany stolik nie istnieje.');
            }
            if ((int)$capacity < $people) {
                throw new InvalidArgumentException('Wybrany stolik jest za mały dla tej liczby osób.');
            }

            $slot = $this->validateSlot($rawDate, $rawTime);
            [$windowStart, $windowEnd] = $this->collisionWindow($slot);
            if ($this->reservationRepository->hasConflict($tableId, $windowStart, $windowEnd)) {
                throw new InvalidArgumentException('Ten stolik został już zarezerwowany. Wybierz inny termin lub stolik.');
            }

            $this->reservationRepository->create($accountId, $tableId, $slot);
            $this->reservationRepository->commit();
        } catch (Throwable $exception) {
            $this->reservationRepository->rollBack();

            throw $exception;
        }
    }

    /**
     * @throws DateMalformedStringException
     */
    private function validateDate(mixed $rawDate): DateTimeImmutable
    {
        if (!is_string($rawDate) || trim($rawDate) === '') {
            throw new InvalidArgumentException('Wybierz poprawną datę rezerwacji.');
        }

        $value = trim($rawDate);
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, $this->timezone());
        $errors = DateTimeImmutable::getLastErrors();

        if ($date === false
            || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $date->format('Y-m-d') !== $value
        ) {
            throw new InvalidArgumentException('Podana data jest nieprawidłowa.');
        }

        $today = new DateTimeImmutable('today', $this->timezone());
        $lastDate = $today->modify('+' . self::MAX_DAYS_AHEAD . ' days');
        if ($date < $today || $date > $lastDate) {
            throw new InvalidArgumentException('Rezerwację można złożyć od dziś do ' . self::MAX_DAYS_AHEAD . ' dni naprzód.');
        }

        return $date;
    }

    private function validatePeople(mixed $rawPeople): int
    {
        $people = $this->validatePositiveInteger(
            $rawPeople,
            'Podaj liczbę osób od 1 do ' . self::MAX_PARTY_SIZE . '.'
        );

        if ($people > self::MAX_PARTY_SIZE) {
            throw new InvalidArgumentException('Maksymalna liczba osób w rezerwacji to ' . self::MAX_PARTY_SIZE . '.');
        }

        return $people;
    }

    private function validatePositiveInteger(mixed $value, string $message): int
    {
        if (!is_int($value) && !is_string($value)) {
            throw new InvalidArgumentException($message);
        }

        $number = filter_var($value, FILTER_VALIDATE_INT);
        if ($number === false || $number < 1) {
            throw new InvalidArgumentException($message);
        }

        return $number;
    }

    /**
     * @throws DateMalformedStringException
     */
    private function validateSlot(mixed $rawDate, mixed $rawTime): DateTimeImmutable
    {
        $date = $this->validateDate($rawDate);

        if (!is_string($rawTime) || preg_match('/^\d{2}:\d{2}$/D', $rawTime) !== 1) {
            throw new InvalidArgumentException('Wybierz poprawną godzinę rezerwacji.');
        }

        $slot = DateTimeImmutable::createFromFormat(
            '!Y-m-d H:i',
            $date->format('Y-m-d') . ' ' . $rawTime,
            $this->timezone()
        );
        $errors = DateTimeImmutable::getLastErrors();
        if ($slot === false
            || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $slot->format('H:i') !== $rawTime
        ) {
            throw new InvalidArgumentException('Podana godzina jest nieprawidłowa.');
        }

        $opening = $this->timeOnDate($date, self::OPENING_TIME);
        $closing = $this->timeOnDate($date, self::CLOSING_TIME);
        $lastStart = $closing->modify('-' . $this->getReservationDurationMinutes() . ' minutes');
        $slotOffset = (int)(($slot->getTimestamp() - $opening->getTimestamp()) / 60);
        $minimumStart = new DateTimeImmutable('now', $this->timezone())
            ->modify('+' . self::MINIMUM_ADVANCE_MINUTES . ' minutes');

        if ($slot < $opening
            || $slot > $lastStart
            || $slotOffset % self::SLOT_INTERVAL_MINUTES !== 0
            || $slot < $minimumStart
        ) {
            throw new InvalidArgumentException('Wybrany termin jest niedostępny. Wybierz jeden z dostępnych terminów.');
        }

        return $slot;
    }

    /**
     * @return array{0: string, 1: string}
     * @throws DateMalformedStringException
     */
    private function collisionWindow(DateTimeImmutable $slot): array
    {
        $duration = $this->getReservationDurationMinutes();

        return [
            $slot->modify('-' . $duration . ' minutes')->format('Y-m-d H:i:s'),
            $slot->modify('+' . $duration . ' minutes')->format('Y-m-d H:i:s')
        ];
    }

    private function timeOnDate(DateTimeImmutable $date, string $time): DateTimeImmutable
    {
        [$hour, $minute] = array_map('intval', explode(':', $time));

        return $date->setTime($hour, $minute);
    }

    private function timezone(): DateTimeZone
    {
        return new DateTimeZone(self::TIMEZONE);
    }
}
