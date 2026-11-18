<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\MenuService;
use InvalidArgumentException;
use Velo\Http\Responses\Concrete\FileResponse\FileResponse;
use Velo\Http\Responses\Concrete\TextResponse;
use Velo\Http\Responses\Concrete\ViewResponse;
use Velo\Http\Responses\Response;

class MenuController
{
    public function __construct(private MenuService $menuService)
    {
    }

    public function __invoke(): Response
    {
        $currentMenu = $this->menuService->getCurrentMenu();

        return new ViewResponse('menu.php', [
            'menuModifiedAt' => $currentMenu['modifiedAt'],
        ]);
    }

    public function preview(): Response
    {
        try {
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
        } catch (InvalidArgumentException $exception) {
            return new TextResponse($exception->getMessage(), 404);
        }
    }
}