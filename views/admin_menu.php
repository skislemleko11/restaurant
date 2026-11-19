<?php
declare(strict_types=1);

$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

/**
 * @var string $token
 * @var array{exists: bool, modifiedAt: string|null} $currentMenu
 * @var list<array{name: string, modifiedAt: string|null}> $archive
 * @var list<string> $errors
 * @var list<string> $successMessages
 */
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie menu - Smaki Miasta</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="icon" type="image/x-icon" href="/imgs/logo.ico">
</head>
<body>
<?php require __DIR__ . '/templates/header.php'; ?>
<main class="admin_menu_main" id="main-content">
    <h1 class="admin_page_title">Zarządzanie menu</h1>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error" role="alert"><?= $escape($error) ?></div>
    <?php endforeach; ?>
    <?php foreach ($successMessages as $message): ?>
        <div class="alert alert-success" role="status"><?= $escape($message) ?></div>
    <?php endforeach; ?>

    <section class="schedule_card menu_card menu_current_card">
        <h2>Aktualnie wgrane menu</h2>
        <div class="menu_current_content">
            <?php if ($currentMenu['exists']): ?>
                <div class="menu_pdf_icon" aria-hidden="true">PDF</div>
                <div class="menu_current_meta">
                    <strong>menu.pdf</strong>
                    <p>Data modyfikacji: <?= $escape($currentMenu['modifiedAt'] ?? 'nieznana') ?></p>
                    <a class="download_menu_btn" href="/admin/menu/preview" target="_blank">Podgląd aktualnego PDF</a>
                </div>
            <?php else: ?>
                <p class="menu_empty_message">Nie wgrano jeszcze aktualnego pliku PDF.</p>
            <?php endif; ?>
        </div>

        <form method="post" action="/admin/menu/upload" enctype="multipart/form-data" class="menu_upload_form">
            <input type="hidden" name="csrf_token" value="<?= $escape($token) ?>">
            <label for="menu-file">Wgraj nowe menu <span>PDF, maks. 10 MB</span></label>
            <input id="menu-file" type="file" name="menu" accept=".pdf,application/pdf" required>
            <button type="submit">Wgraj nowe menu</button>
        </form>
    </section>

    <section class="schedule_card menu_card menu_archive_card">
        <h2>Historia poprzednich wersji</h2>
        <?php if ($archive === []): ?>
            <p class="menu_empty_message">Brak archiwalnych wersji menu.</p>
        <?php else: ?>
            <div class="menu_archive">
                <?php foreach ($archive as $version): ?>
                    <article class="menu_archive_item">
                        <div class="menu_archive_name">
                            <span class="menu_archive_pdf" aria-hidden="true">PDF</span>
                            <div>
                            <strong><?= $escape($version['name']) ?></strong>
                            <p><?= $escape($version['modifiedAt'] ?? 'Data nieznana') ?></p>
                            </div>
                        </div>
                        <div class="menu_archive_actions">
                            <a href="/admin/menu/archive/<?= urlencode($version['name']) ?>/preview" target="_blank">Podgląd</a>
                            <form method="post" action="/admin/menu/archive/<?= urlencode($version['name']) ?>/delete"
                                  class="inline_form" onsubmit="return confirm('Czy na pewno usunąć tę wersję menu?');">
                                <input type="hidden" name="csrf_token" value="<?= $escape($token) ?>">
                                <button type="submit" class="danger_button">Usuń</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/templates/footer.html'; ?>
<script src="/js/color_theme.js"></script>
</body>
</html>
