<?php
declare(strict_types=1);

namespace App\Controllers\Registration;

use App\Services\Registration\RegistrationException;
use App\Services\Registration\RegistrationService;
use DateMalformedStringException;
use Random\RandomException;
use Velo\Http\Request;
use Velo\Http\Responses\Concrete\ViewResponse;
use Velo\Http\Responses\Response;
use Velo\Session\FlashMessages\FlashMessagesInterface;

final readonly class RegisterController
{
    public function __construct(
        private RegistrationService $registrationService,
        private FlashMessagesInterface $flashMessages
    ) {
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

        if ($this->flashMessages->has(FlashMessagesInterface::ERROR)) {
            return new ViewResponse('register.php', [
                'email' => $email,
            ], 422);
        }

        try {
            $this->registrationService->register($email, $password);
        } catch (RegistrationException $exception) {
            $this->flashMessages->error($exception->getMessage());
            return new ViewResponse('register.php', [
                'email' => $email,
            ], 422);
        }

        return new ViewResponse('registration_success.php');
    }
}