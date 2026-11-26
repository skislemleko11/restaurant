<?php
declare(strict_types=1);

namespace App\Controllers\Registration;

use App\Services\Registration\RegistrationException;
use App\Services\Registration\RegistrationService;
use DateMalformedStringException;
use Random\RandomException;
use Velo\Http\Request;
use Velo\Http\Responses\Concrete\RedirectResponse;
use Velo\Http\Responses\Concrete\ViewResponse;
use Velo\Http\Responses\Response;
use Velo\Middlewares\AntiCsrf\AntiCsrfTokenManagerInterface;
use Velo\Session\FlashMessages\FlashMessagesInterface;
use Velo\Session\Session\SessionInterface;

final readonly class RegisterController
{
    public function __construct(
        private RegistrationService           $registrationService,
        private AntiCsrfTokenManagerInterface $tokenManager,
        private SessionInterface              $session,
        private FlashMessagesInterface        $flashMessages
    )
    {
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function __invoke(Request $request): Response
    {
        $email = trim((string)$request->getFormValue('email', ''));
        $password = (string)$request->getFormValue('password', '');
        $passwordConfirmation = (string)$request->getFormValue('password_confirmation', '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flashMessages->error('Podaj poprawny adres email.');
        }

        if (strlen($password) < 8) {
            $this->flashMessages->error('Hasło musi mieć co najmniej 8 znaków.');
        }

        if ($password !== $passwordConfirmation) {
            $this->flashMessages->error('Hasła muszą być identyczne.');
        }

        $token = $this->tokenManager->getToken();
        $this->session->setFlash('email', $email);

        if ($this->flashMessages->has(FlashMessagesInterface::ERROR)) {
            return new ViewResponse('register.php', [
                'email' => $email,
                'token' => $token
            ], 422);
        }

        try {
            $this->registrationService->register($email, $password);
        } catch (RegistrationException $exception) {
            $this->flashMessages->error($exception->getMessage());
            return new ViewResponse('register.php', [
                'token' => $token
            ], 409);
        }

        $this->session->setFlash('success', 'Na twój adres email został wysłany link weryfikacyjny.');

        return new RedirectResponse('/register');
    }
}