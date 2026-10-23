<?php
declare(strict_types=1);

namespace App\Controllers;

use Velo\Http\Responses\Concrete\ViewResponse;
use Velo\Http\Responses\Response;

class RegistryController
{
    public function showRegister(): Response
    {
        return new ViewResponse('register.php');
    }
}