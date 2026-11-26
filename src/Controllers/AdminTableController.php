<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\TableService;
use InvalidArgumentException;
use Velo\Http\Request;
use Velo\Http\Responses\Concrete\RedirectResponse;
use Velo\Http\Responses\Concrete\ViewResponse;
use Velo\Http\Responses\Response;
use Velo\Middlewares\AntiCsrf\AntiCsrfTokenManagerInterface;
use Velo\Session\FlashMessages\FlashMessagesInterface;

final readonly class AdminTableController
{
    public function __construct(
        private TableService                  $tableService,
        private AntiCsrfTokenManagerInterface $tokenManager,
        private FlashMessagesInterface        $flashMessages
    ) {
    }

    public function __invoke(): ViewResponse
    {
        return new ViewResponse('admin_tables.php', [
            'token' => $this->tokenManager->getToken(),
            'tables' => $this->tableService->getTables(),
            'errors' => $this->flashMessages->get(FlashMessagesInterface::ERROR),
            'successMessages' => $this->flashMessages->get(FlashMessagesInterface::SUCCESS),
        ]);
    }

    public function store(Request $request): Response
    {
        return $this->execute(
            fn() => $this->tableService->create(
                $request->getFormValue('name'),
                $request->getFormValue('location'),
                $request->getFormValue('capacity'),
                $request->getFormValue('is_active')
            ),
            'Stolik został dodany.'
        );
    }

    public function update(Request $request, int $id): Response
    {
        return $this->execute(
            fn() => $this->tableService->update(
                $id,
                $request->getFormValue('name'),
                $request->getFormValue('location'),
                $request->getFormValue('capacity'),
                $request->getFormValue('is_active')
            ),
            'Stolik został zmieniony.'
        );
    }

    public function delete(Request $request, int $id): Response
    {
        return $this->execute(
            fn() => $this->tableService->delete($id),
            'Stolik został usunięty.'
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

        return new RedirectResponse('/admin/tables', 303);
    }
}
