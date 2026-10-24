<?php
declare(strict_types=1);

namespace App\Controllers\Registration;

use App\Services\Registration\EmailVerificationService;
use Velo\Http\Request;
use Velo\Http\Responses\Concrete\ViewResponse;

final readonly class VerifyEmailController
{
    public function __construct(
        private EmailVerificationService $verificationService
    )
    {
    }

    public function __invoke(Request $request): ViewResponse
    {
        $token = $request->urlParams['token'] ?? '';

        if ($token === '' || !$this->verificationService->verify($token)) {
            return new ViewResponse('email_verification.php', [
                'success' => false,
                'message' => 'Link weryfikacyjny jest nieprawidłowy, wygasł lub został już użyty.',
            ], 400);
        }

        return new ViewResponse('email_verification.php', [
            'success' => true,
            'message' => 'Adres email został pomyślnie zweryfikowany.',
        ]);
    }
}
