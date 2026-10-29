<?php
declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Services\AuthService;
use Velo\Http\Request;
use Velo\Http\Responses\Concrete\RedirectResponse;
use Velo\Http\Responses\Concrete\ViewResponse;
use Velo\Http\Responses\Response;
use Velo\Middlewares\AntiCsrf\AntiCsrfTokenManagerInterface;
use Velo\Session\FlashMessages\FlashMessagesInterface;
use Velo\Session\Session\SessionInterface;

final readonly class ActionAuthController
{
    public function __construct(
        private AuthService $authService,
        private AntiCsrfTokenManagerInterface $tokenManager,
        private SessionInterface $session,
        private FlashMessagesInterface $flashMessages
    )
    {
    }

    public function login(Request $request): Response
    {
        $email = trim((string)$request->getFormValue('email', ''));
        $password = (string)$request->getFormValue('password', '');
        $token = $this->tokenManager->getToken();
        $this->session->setFlash('email', $email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $this->flashMessages->error('Nieprawidłowy email lub hasło.');
            return new ViewResponse('login.php', ['token' => $token], 422);
        }

        $userId = $this->authService->authenticate($email, $password);

        if ($userId === null) {
            $this->flashMessages->error('Nieprawidłowy email lub hasło.');
            return new ViewResponse('login.php', ['token' => $token], 422);
        }

        session_regenerate_id(true);
        $this->session->set(AuthService::USER_ID_SESSION_KEY, $userId);

        return new RedirectResponse('/');
    }
}