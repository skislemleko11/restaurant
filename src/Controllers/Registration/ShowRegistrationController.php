<?php
declare(strict_types=1);

namespace App\Controllers\Registration;

use Velo\Http\Responses\Concrete\ViewResponse;

final class ShowRegistrationController
{
    public function __invoke(): ViewResponse
    {
        return new ViewResponse('register.php');
    }
}