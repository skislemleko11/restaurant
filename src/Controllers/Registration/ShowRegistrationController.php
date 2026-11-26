<?php
declare(strict_types=1);

namespace App\Controllers\Registration;

use Velo\Http\Responses\Concrete\ViewResponse;
use Velo\Middlewares\AntiCsrf\AntiCsrfTokenManagerInterface;

final readonly class ShowRegistrationController
{
    public function __construct(private AntiCsrfTokenManagerInterface $tokenManager)
    {
    }

    public function __invoke(): ViewResponse
    {
        return new ViewResponse('register.php', ['token' => $this->tokenManager->getToken()]);
    }
}