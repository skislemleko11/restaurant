<?php
declare(strict_types=1);

namespace Velo\View;

use Velo\View\ViewResolver\Exceptions\InvalidViewExtensionException;
use Velo\View\ViewResolver\Exceptions\ViewNotFoundException;

interface ViewRendererInterface
{
    /**
     * @return string Content to echo.
     *
     * @throws ViewNotFoundException
     * @throws InvalidViewExtensionException
     */
    public function render(string $viewFile, array $dataToExtract = []): string;
}