<?php
declare(strict_types=1);

namespace App\Controllers\Auth;

use Velo\Http\Request;
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

    public function forgotPassword(): ViewResponse
    {
        return $this->getResponse('forgot_password.php');
    }

    public function resetPassword(Request $request, string $token): ViewResponse
    {
        return new ViewResponse('reset_password.php', [
            'token' => $token,
            'csrfToken' => $this->antiCsrfTokenManager->getToken(),
        ]);
    }

    private function getResponse(string $viewFile): ViewResponse
    {
        return new ViewResponse($viewFile,
            ['token' => $this->antiCsrfTokenManager->getToken()]
        );
    }
}