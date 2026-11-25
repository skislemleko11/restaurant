<?php
declare(strict_types=1);

/**
 * @var list<array{reservation_id: int, date_of_reservation: string, number_of_people: int, table_name: string, location: string}> $reservations
 * @var string $token
 * @var list<string> $errors
 * @var list<string> $successMessages
 */
$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$now = new DateTimeImmutable('now', new DateTimeZone('Europe/Warsaw'));
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Moje rezerwacje</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="icon" type="image/x-icon" href="/imgs/logo.ico">
</head>
<body>
<?php require __DIR__ . '/templates/header.php'; ?>
<main class="admin_schedule_main" id="main-content">
    <h1 class="admin_page_title">Moje rezerwacje</h1>
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error" role="alert"><?= $escape($error) ?></div>
    <?php endforeach; ?>
    <?php foreach ($successMessages as $message): ?>
        <div class="alert alert-success" role="status"><?= $escape($message) ?></div>
    <?php endforeach; ?>
    <?php if ($reservations === []): ?>
        <section class="reservation_empty_card" role="status">
            <h2>Brak rezerwacji</h2>
            <p>Nie masz jeszcze żadnych rezerwacji.</p>
            <a class="reservation_link_button" href="/reservation">Zarezerwuj stolik</a>
        </section>
    <?php else: ?>
        <section class="reservation_list">
            <?php foreach ($reservations as $reservation): ?>
                <?php $date = new DateTimeImmutable($reservation['date_of_reservation'], new DateTimeZone('Europe/Warsaw')); ?>
                <article class="reservation_history_card">
                    <div class="blocked_period_heading">
                        <strong><?= $escape($date->format('d.m.Y, H:i')) ?></strong>
                        <div class="reservation_card_actions">
                            <span class="table_status <?= $date >= $now ? 'table_status_active' : 'table_status_inactive' ?>">
                                <?= $date >= $now ? 'Nadchodząca' : 'Archiwalna' ?>
                            </span>
                            <?php if ($date >= $now): ?>
                                <form method="post"
                                      action="/my-reservations/<?= $reservation['reservation_id'] ?>/cancel"
                                      class="reservation_cancel_form"
                                      onsubmit="return confirm('Anulować tę rezerwację?')">
                                    <input type="hidden" name="csrf_token" value="<?= $escape($token) ?>">
                                    <button type="submit" class="reservation_cancel_button">
                                        <span aria-hidden="true">×</span> Anuluj
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p><strong><?= $escape($reservation['table_name']) ?></strong> · <?= $escape($reservation['location']) ?></p>
                    <p>Liczba osób: <?= $reservation['number_of_people'] ?></p>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/templates/footer.html'; ?>
<script src="/js/color_theme.js"></script>
</body>
</html>
