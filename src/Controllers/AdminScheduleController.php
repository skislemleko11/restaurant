<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\BlockedPeriodService;
use InvalidArgumentException;
use Velo\Http\Request;
use Velo\Http\Responses\Concrete\RedirectResponse;
use Velo\Http\Responses\Concrete\ViewResponse;
use Velo\Http\Responses\Response;
use Velo\Middlewares\AntiCsrf\AntiCsrfTokenManagerInterface;
use Velo\Session\FlashMessages\FlashMessagesInterface;

final readonly class AdminScheduleController
{
    public function __construct(
        private BlockedPeriodService          $blockedPeriodService,
        private AntiCsrfTokenManagerInterface $tokenManager,
        private FlashMessagesInterface        $flashMessages
    )
    {
    }

    public function __invoke(): ViewResponse
    {
        return new ViewResponse('admin_schedule.php', [
            'token' => $this->tokenManager->getToken(),
            'blocks' => $this->blockedPeriodService->getCalendar(),
            'errors' => $this->flashMessages->get(FlashMessagesInterface::ERROR),
            'successMessages' => $this->flashMessages->get(FlashMessagesInterface::SUCCESS),
        ]);
    }

    public function store(Request $request): Response
    {
        return $this->execute(
            function () use ($request): void {
                $this->blockedPeriodService->create(
                    $request->getFormValue('start_date'),
                    $request->getFormValue('end_date'),
                    $request->getFormValue('start_time'),
                    $request->getFormValue('end_time'),
                    $request->getFormValue('reason')
                );
            },
            'Blokada terminu została dodana.'
        );
    }

    public function update(Request $request, int $id): Response
    {
        return $this->execute(
            function () use ($request, $id): void {
                $this->blockedPeriodService->update(
                    $id,
                    $request->getFormValue('start_date'),
                    $request->getFormValue('end_date'),
                    $request->getFormValue('start_time'),
                    $request->getFormValue('end_time'),
                    $request->getFormValue('reason')
                );
            },
            'Blokada terminu została zmieniona.'
        );
    }

    public function delete(Request $request, int $id): Response
    {
        return $this->execute(
            function () use ($id): void {
                $this->blockedPeriodService->delete($id);
            },
            'Blokada terminu została usunięta.'
        );
    }

    private function execute(callable $action, string $success): Response
    {
        try {
            $action();
            $this->flashMessages->success($success);
        } catch (InvalidArgumentException $exception) {
            $this->flashMessages->error($exception->getMessage());
        }

        return new RedirectResponse('/admin/schedule', 303);
    }
}
