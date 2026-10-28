<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\ReservationService;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Velo\Http\Request;
use Velo\Http\Responses\Concrete\JsonResponse;
use Velo\Http\Responses\Concrete\RedirectResponse;
use Velo\Http\Responses\Concrete\ViewResponse;
use Velo\Http\Responses\Response;
use Velo\Middlewares\AntiCsrf\AntiCsrfTokenManagerInterface;
use Velo\Session\FlashMessages\FlashMessagesInterface;
use Velo\Session\Session\SessionInterface;

final readonly class ReservationController
{
    public function __construct(
        private ReservationService $reservationService,
        private AntiCsrfTokenManagerInterface $tokenManager,
        private FlashMessagesInterface $flashMessages,
        private SessionInterface $session
    ) {
    }

    public function __invoke(): ViewResponse
    {
        $today = new DateTimeImmutable('today', new DateTimeZone(ReservationService::TIMEZONE));

        return new ViewResponse('reservation.php', [
            'token' => $this->tokenManager->getToken(),
            'errors' => $this->flashMessages->get(FlashMessagesInterface::ERROR),
            'successMessages' => $this->flashMessages->get(FlashMessagesInterface::SUCCESS),
            'minimumDate' => $today->format('Y-m-d'),
            'initialDate' => $today->format('Y-m-d'),
            'maximumDate' => $today->modify('+' . ReservationService::MAX_DAYS_AHEAD . ' days')->format('Y-m-d'),
            'maximumPartySize' => ReservationService::MAX_PARTY_SIZE
        ]);
    }

    public function slots(Request $request): JsonResponse
    {
        try {
            return new JsonResponse([
                'slots' => $this->reservationService->getSlots(
                    $request->urlParams['date'] ?? null,
                    $request->urlParams['people'] ?? null
                )
            ]);
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 400);
        }
    }

    public function tables(Request $request): JsonResponse
    {
        try {
            return new JsonResponse([
                'tables' => $this->reservationService->getTables(
                    $request->urlParams['date'] ?? null,
                    $request->urlParams['time'] ?? null,
                    $request->urlParams['people'] ?? null
                )
            ]);
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 400);
        }
    }

    public function store(Request $request): Response
    {
        try {
            $accountId = $this->session->has('account_id')
                ? $this->sessionAccountId($this->session->get('account_id'))
                : ReservationService::DEV_ACCOUNT_ID;

            $this->reservationService->createReservation(
                $accountId,
                $request->getFormValue('date'),
                $request->getFormValue('time'),
                $request->getFormValue('table_id'),
                $request->getFormValue('people')
            );

            $this->flashMessages->success('Rezerwacja została zapisana. Do zobaczenia!');
        } catch (InvalidArgumentException $exception) {
            $this->flashMessages->error($exception->getMessage());
        }

        return new RedirectResponse('/reservation', 303);
    }

    private function sessionAccountId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            throw new InvalidArgumentException('Sesja logowania jest nieprawidłowa. Zaloguj się ponownie.');
        }

        $accountId = filter_var($value, FILTER_VALIDATE_INT);
        if ($accountId === false || $accountId < 1) {
            throw new InvalidArgumentException('Sesja logowania jest nieprawidłowa. Zaloguj się ponownie.');
        }

        return $accountId;
    }
}
