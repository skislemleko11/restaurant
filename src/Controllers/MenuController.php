<?php
declare(strict_types=1);

namespace App\Controllers;

use Velo\Http\Responses\Concrete\ViewResponse;
use Velo\Http\Responses\Response;

class MenuController
{
    public function __invoke(): Response
    {
        return new ViewResponse('menu.php');
    }
}