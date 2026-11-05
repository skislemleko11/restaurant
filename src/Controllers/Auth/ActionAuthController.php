<?php
declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Services\Auth\PasswordResetException;
use App\Services\Auth\PasswordResetService;
use App\Services\AuthService;
use Random\RandomException;
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
        private FlashMessagesInterface $flashMessages,
        private PasswordResetService $passwordResetService
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

    public function logout(): Response
    {
        $this->session->remove(AuthService::USER_ID_SESSION_KEY);
        session_regenerate_id(true);

        return new RedirectResponse('/');
    }

    /**
     * @throws \DateMalformedStringException
     * @throws RandomException
     */
    public function requestPasswordReset(Request $request): Response
    {
        $email = trim((string)$request->getFormValue('email', ''));
        $token = $this->tokenManager->getToken();

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flashMessages->error('Podaj poprawny adres email.');
            return new ViewResponse('forgot_password.php', ['token' => $token], 422);
        }

        try {
            $this->passwordResetService->requestReset($email);
        } catch (PasswordResetException $exception) {
            $this->flashMessages->error($exception->getMessage());
            return new ViewResponse('forgot_password.php', ['token' => $token], 422);
        }

        $this->session->setFlash(
            'success',
            'Jeśli konto z tym adresem istnieje, otrzymasz wiadomość z instrukcją resetu hasła.'
        );

        return new RedirectResponse('/forgot-password');
    }

    public function resetPassword(Request $request): Response
    {
        $token = (string)$request->getFormValue('token', '');
        $password = (string)$request->getFormValue('password', '');
        $passwordConfirmation = (string)$request->getFormValue('password_confirmation', '');
        $csrfToken = $this->tokenManager->getToken();

        if (strlen($password) < 8 || $password !== $passwordConfirmation) {
            $this->flashMessages->error(
                strlen($password) < 8
                    ? 'Hasło musi mieć co najmniej 8 znaków.'
                    : 'Hasła muszą być identyczne.'
            );
            return new ViewResponse('reset_password.php', [
                'token' => $token,
                'csrfToken' => $csrfToken,
            ], 422);
        }

        try {
            $this->passwordResetService->reset($token, $password);
        } catch (PasswordResetException $exception) {
            $this->flashMessages->error($exception->getMessage());
            return new ViewResponse('reset_password.php', [
                'token' => $token,
                'csrfToken' => $csrfToken,
            ], 422);
        }

        $this->flashMessages->success('Hasło zostało zmienione. Możesz się teraz zalogować.');

        return new RedirectResponse('/login');
    }
}