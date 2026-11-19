<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Services\MenuService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MenuService::class)]
final class MenuServiceTest extends TestCase
{
    private string $publicDirectory;

    protected function setUp(): void
    {
        $this->publicDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'restaurant-menu-' . bin2hex(random_bytes(4));
        mkdir($this->publicDirectory . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . 'archive', 0777, true);
    }

    protected function tearDown(): void
    {
        $archive = $this->publicDirectory . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . 'archive';
        foreach (glob($archive . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
            unlink($file);
        }
        if (is_dir($archive)) {
            rmdir($archive);
        }

        $pdfDirectory = $this->publicDirectory . DIRECTORY_SEPARATOR . 'pdf';
        if (is_dir($pdfDirectory)) {
            rmdir($pdfDirectory);
        }
        rmdir($this->publicDirectory);
    }

    public function testReportsMissingCurrentMenu(): void
    {
        $service = new MenuService($this->publicDirectory);

        self::assertSame(
            ['exists' => false, 'modifiedAt' => null],
            $service->getCurrentMenu()
        );
    }

    public function testReportsArchiveVersions(): void
    {
        $archive = $this->publicDirectory . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . 'archive';
        file_put_contents($archive . DIRECTORY_SEPARATOR . 'menu-20261118-120000-abcd12.pdf', '%PDF-1.4');

        $service = new MenuService($this->publicDirectory);
        $versions = $service->getArchive();

        self::assertCount(1, $versions);
        self::assertSame('menu-20261118-120000-abcd12.pdf', $versions[0]['name']);
    }

    public function testRejectsArchiveNameOutsideExpectedPattern(): void
    {
        $service = new MenuService($this->publicDirectory);

        $this->expectException(\InvalidArgumentException::class);
        $service->deleteArchive('../menu.pdf');
    }
}
