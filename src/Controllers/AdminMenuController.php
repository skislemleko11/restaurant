<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\MenuService;
use InvalidArgumentException;
use Random\RandomException;
use RuntimeException;
use Velo\Http\Request;
use Velo\Http\Responses\Concrete\RedirectResponse;
use Velo\Http\Responses\Concrete\FileResponse\FileResponse;
use Velo\Http\Responses\Concrete\TextResponse;
use Velo\Http\Responses\Concrete\ViewResponse;
use Velo\Http\Responses\Response;
use Velo\Middlewares\AntiCsrf\AntiCsrfTokenManagerInterface;
use Velo\Session\FlashMessages\FlashMessagesInterface;

final readonly class AdminMenuController
{
    public function __construct(
        private MenuService $menuService,
        private AntiCsrfTokenManagerInterface $tokenManager,
        private FlashMessagesInterface $flashMessages
    ) {
    }

    public function __invoke(): ViewResponse
    {
        return new ViewResponse('admin_menu.php', [
            'token' => $this->tokenManager->getToken(),
            'currentMenu' => $this->menuService->getCurrentMenu(),
            'archive' => $this->menuService->getArchive(),
            'errors' => $this->flashMessages->get(FlashMessagesInterface::ERROR),
            'successMessages' => $this->flashMessages->get(FlashMessagesInterface::SUCCESS),
        ]);
    }

    public function upload(Request $request): Response
    {
        try {
            $this->menuService->upload($_FILES['menu'] ?? []);
            $this->flashMessages->success('Nowe menu zostało przesłane i jest widoczne dla klientów.');
        } catch (InvalidArgumentException | RuntimeException | RandomException $exception) {
            $this->flashMessages->error($exception->getMessage());
        }

        return new RedirectResponse('/admin/menu', 303);
    }

    public function deleteArchive(Request $request, string $name): Response
    {
        try {
            $this->menuService->deleteArchive($name);
            $this->flashMessages->success('Wybrana wersja menu została usunięta.');
        } catch (InvalidArgumentException | RuntimeException $exception) {
            $this->flashMessages->error($exception->getMessage());
        }

        return new RedirectResponse('/admin/menu', 303);
    }

    public function preview(): Response
    {
        $path = $this->menuService->getCurrentPath();
        if (!is_file($path)) {
            return new TextResponse('Aktualne menu nie zostało wgrane.', 404);
        }

        return new FileResponse($path, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="menu.pdf"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function previewArchive(Request $request, string $name): Response
    {
        try {
            $path = $this->menuService->getArchivePath($name);
            return new FileResponse($path, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $name . '"',
                'Cache-Control' => 'no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (InvalidArgumentException $exception) {
            return new TextResponse($exception->getMessage(), 404);
        }
    }
}
