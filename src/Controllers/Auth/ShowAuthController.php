<?php
declare(strict_types=1);

namespace App\Controllers\Auth;

use Velo\Http\Responses\Concrete\ViewResponse;
use Velo\Middlewares\AntiCsrf\AntiCsrfTokenManagerInterface;

final readonly class ShowAuthController
{
    public function __construct(
        private AntiCsrfTokenManagerInterface $antiCsrfTokenManager
    )
    {
    }

    public function login(): ViewResponse
    {
        return $this->getResponse('login.php');
    }

    public function logout(): ViewResponse
    {
        return $this->getResponse('logout.php');
    }

    private function getResponse(string $viewFile): ViewResponse
    {
        return new ViewResponse($viewFile,
            ['token' => $this->antiCsrfTokenManager->getToken()]
        );
    }
}