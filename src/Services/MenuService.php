<?php
declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;

final class MenuService
{
    public const int MAX_FILE_SIZE = 10 * 1024 * 1024;

    private string $menuDirectory;
    private string $archiveDirectory;

    public function __construct(?string $publicDirectory = null)
    {
        $root = $publicDirectory ?? dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public';
        $this->menuDirectory = $root . DIRECTORY_SEPARATOR . 'pdf';
        $this->archiveDirectory = $this->menuDirectory . DIRECTORY_SEPARATOR . 'archive';
    }

    public function getCurrentMenu(): array
    {
        $path = $this->menuDirectory . DIRECTORY_SEPARATOR . 'menu.pdf';

        if (!is_file($path)) {
            return ['exists' => false, 'modifiedAt' => null];
        }

        $timestamp = filemtime($path);

        return [
            'exists' => true,
            'modifiedAt' => $timestamp === false ? null : date('Y-m-d H:i:s', $timestamp),
        ];
    }

    public function getCurrentPath(): string
    {
        return $this->menuDirectory . DIRECTORY_SEPARATOR . 'menu.pdf';
    }

    public function getArchivePath(string $name): string
    {
        if (!preg_match('/^menu-[A-Za-z0-9.-]+\.pdf$/', $name)) {
            throw new InvalidArgumentException('Nieprawidłowa nazwa pliku archiwum.');
        }

        $path = $this->archiveDirectory . DIRECTORY_SEPARATOR . $name;
        $realArchive = realpath($this->archiveDirectory);
        $realFile = realpath($path);
        if ($realArchive === false || $realFile === false ||
            !str_starts_with($realFile, rtrim($realArchive, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)) {
            throw new InvalidArgumentException('Nie znaleziono wskazanej wersji menu.');
        }

        return $realFile;
    }

    public function getArchive(): array
    {
        if (!is_dir($this->archiveDirectory)) {
            return [];
        }

        $files = glob($this->archiveDirectory . DIRECTORY_SEPARATOR . 'menu-*.pdf');
        if ($files === false) {
            return [];
        }

        $versions = [];
        foreach ($files as $path) {
            $timestamp = filemtime($path);
            $versions[] = [
                'name' => basename($path),
                'modifiedAt' => $timestamp === false ? null : date('Y-m-d H:i:s', $timestamp),
            ];
        }

        usort($versions, static fn(array $a, array $b): int =>
            strcmp((string)($b['modifiedAt'] ?? ''), (string)($a['modifiedAt'] ?? ''))
        );

        return $versions;
    }

    public function upload(array $file): void
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException($this->uploadErrorMessage((int)$file['error']));
        }

        $tmpName = (string)($file['tmp_name'] ?? '');
        $size = (int)($file['size'] ?? 0);
        $name = (string)($file['name'] ?? '');

        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new InvalidArgumentException('Nie udało się odebrać przesłanego pliku.');
        }
        if ($size > self::MAX_FILE_SIZE) {
            throw new InvalidArgumentException('Plik przekracza maksymalny rozmiar 10 MB.');
        }

        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $mimeType = mime_content_type($tmpName);
        if ($extension !== 'pdf' || $mimeType !== 'application/pdf') {
            throw new InvalidArgumentException('Dozwolony jest wyłącznie plik w formacie PDF.');
        }

        $header = file_get_contents($tmpName, false, null, 0, 5);
        if ($header !== '%PDF-') {
            throw new InvalidArgumentException('Przesłany plik nie zawiera poprawnego dokumentu PDF.');
        }

        if (!is_dir($this->menuDirectory) && !mkdir($this->menuDirectory, 0775, true)) {
            throw new RuntimeException('Nie można utworzyć katalogu z menu.');
        }
        if (!is_dir($this->archiveDirectory) && !mkdir($this->archiveDirectory, 0775, true)) {
            throw new RuntimeException('Nie można utworzyć katalogu archiwum menu.');
        }

        $currentPath = $this->menuDirectory . DIRECTORY_SEPARATOR . 'menu.pdf';
        if (is_file($currentPath)) {
            $archiveName = 'menu-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.pdf';
            if (!copy($currentPath, $this->archiveDirectory . DIRECTORY_SEPARATOR . $archiveName)) {
                throw new RuntimeException('Nie udało się zarchiwizować poprzedniej wersji menu.');
            }
        }

        if (!move_uploaded_file($tmpName, $currentPath)) {
            throw new RuntimeException('Nie udało się zapisać nowego menu.');
        }
    }

    public function deleteArchive(string $name): void
    {
        if (!preg_match('/^menu-[A-Za-z0-9.-]+\.pdf$/', $name)) {
            throw new InvalidArgumentException('Nieprawidłowa nazwa pliku archiwum.');
        }

        $path = $this->archiveDirectory . DIRECTORY_SEPARATOR . $name;
        $realArchive = realpath($this->archiveDirectory);
        $realFile = realpath($path);
        if ($realArchive === false || $realFile === false ||
            !str_starts_with($realFile, rtrim($realArchive, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)) {
            throw new InvalidArgumentException('Nie znaleziono wskazanej wersji menu.');
        }
        if (!unlink($realFile)) {
            throw new RuntimeException('Nie udało się usunąć wersji menu.');
        }
    }

    private function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE =>
                'Plik przekracza maksymalny rozmiar 10 MB.',
            UPLOAD_ERR_PARTIAL =>
                'Przesyłanie pliku zostało przerwane.',
            UPLOAD_ERR_NO_FILE =>
                'Nie wybrano pliku PDF.',
            default =>
                'Nie udało się przesłać pliku.',
        };
    }
}
