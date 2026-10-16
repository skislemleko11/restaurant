<?php
declare(strict_types=1);

namespace App\Controllers;

use Velo\Http\Responses\Concrete\TextResponse;
use Velo\Http\Responses\Response;

class HomeController
{
    public function __invoke(): Response
    {
        // Replace with a ViewResponse when homepage is ready
        return new TextResponse('Hello World!');
    }
}