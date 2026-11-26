<?php
declare(strict_types=1);

$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$date = static fn(string $value): string => substr($value, 0, 10);
$time = static fn(string $value): string => substr($value, 11, 5);

/**
 * @var string $token
 * @var list<array{id: int, start: string, end: string, reason: string|null}> $blocks
 * @var list<string> $errors
 * @var list<string> $successMessages
 */
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smaki Miasta - Restauracja</title>
    <link rel="stylesheet" href="/css/style.css">
    <meta name="description"
          content="Smaki Miasta to restauracja oferująca najlepsze dania ze świeżych składników. Sprawdź nasze menu i zarezerwuj stolik online.">
    <meta name="keywords"
          content="restauracja, jedzenie, obiad, kolacja, rezerwacja stolika, menu">
    <meta name="robots" content="all">
    <link rel="icon" type="image/x-icon" href="/imgs/logo.ico">
</head>
<body>
<?php require __DIR__ . '/templates/header.php'; ?>
<main class="admin_schedule_main" id="main-content">
    <h1 class="admin_page_title">Dostępność restauracji</h1>
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error" role="alert"><?= $escape($error) ?></div>
    <?php endforeach; ?>
    <?php foreach ($successMessages as $message): ?>
        <div class="alert alert-success" role="status"><?= $escape($message) ?></div>
    <?php endforeach; ?>

    <section class="schedule_card schedule_admin_card">
        <h2>Dodaj nieczynność</h2>
        <p class="schedule_helper">Wybierz daty i, jeśli to potrzebne, godzinę. Puste pola godzin oznaczają cały dzień.</p>
        <form method="post" action="/admin/schedule/blocks" class="schedule_form">
            <input type="hidden" name="csrf_token" value="<?= $escape($token) ?>">
            <label>Od <input type="date" name="start_date" required></label>
            <label>Do <input type="date" name="end_date" required></label>
            <label>Od godziny <input type="time" name="start_time"></label>
            <label>Do godziny <input type="time" name="end_time"></label>
            <label>Powód (opcjonalnie) <input type="text" name="reason" maxlength="255"></label>
            <button type="submit">Dodaj nieczynność</button>
        </form>
    </section>

    <section class="schedule_card schedule_admin_card">
        <h2>Zablokowane terminy</h2>
        <?php if ($blocks === []): ?>
            <p class="schedule_empty">Brak zaplanowanych blokad w najbliższym roku.</p>
        <?php else: ?>
            <div class="blocked_periods">
                <?php foreach ($blocks as $block): ?>
                    <?php $wholeDay = $time($block['start']) === '00:00' && $time($block['end']) === '23:59'; ?>
                    <article class="blocked_period">
                        <div class="blocked_period_heading">
                            <div class="blocked_period_dates">
                                <span>Nieczynność</span>
                                <strong><?= $escape($date($block['start'])) ?>
                                    – <?= $escape($date($block['end'])) ?></strong>
                            </div>
                            <span class="blocked_badge">ZABLOKOWANE</span>
                        </div>
                        <p class="blocked_period_details"><?= $wholeDay ? 'Cały dzień' : $escape($time($block['start']) . ' – ' . $time($block['end'])) ?>
                            <?php if ($block['reason'] !== null): ?> · <?= $escape($block['reason']) ?><?php endif; ?>
                        </p>
                        <details class="blocked_edit">
                            <summary>Edytuj blokadę</summary>
                            <form method="post" action="/admin/schedule/blocks/<?= $block['id'] ?>/update"
                                  class="schedule_form">
                                <input type="hidden" name="csrf_token" value="<?= $escape($token) ?>">
                                <label>Od <input type="date" name="start_date"
                                                 value="<?= $escape($date($block['start'])) ?>" required></label>
                                <label>Do <input type="date" name="end_date"
                                                 value="<?= $escape($date($block['end'])) ?>" required></label>
                                <label>Od godziny <input type="time" name="start_time"
                                                         value="<?= $wholeDay ? '' : $escape($time($block['start'])) ?>"></label>
                                <label>Do godziny <input type="time" name="end_time"
                                                         value="<?= $wholeDay ? '' : $escape($time($block['end'])) ?>"></label>
                                <label>Powód <input type="text" name="reason" maxlength="255"
                                                    value="<?= $escape($block['reason'] ?? '') ?>"></label>
                                <button type="submit">Zapisz zmiany</button>
                            </form>
                        </details>
                        <form method="post" action="/admin/schedule/blocks/<?= $block['id'] ?>/delete"
                              class="inline_form">
                            <input type="hidden" name="csrf_token" value="<?= $escape($token) ?>">
                            <button type="submit" class="danger_button">Odblokuj termin</button>
                        </form>
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
