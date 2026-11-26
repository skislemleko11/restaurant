<?php
declare(strict_types=1);

/**
 * @var list<array{reservation_id: int, date_of_reservation: string, number_of_people: int, table_name: string, location: string, email: string}> $reservations
 */
$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rezerwacje - panel administracyjny</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="icon" type="image/x-icon" href="/imgs/logo.ico">
</head>
<body>
<?php require __DIR__ . '/templates/header.php'; ?>
<main class="admin_schedule_main" id="main-content">
    <h1 class="admin_page_title">Wszystkie rezerwacje</h1>
    <section class="schedule_card schedule_admin_card">
        <?php if ($reservations === []): ?>
            <p class="schedule_empty">Brak rezerwacji.</p>
        <?php else: ?>
            <div class="reservation_table_wrap">
                <table class="reservation_table">
                    <thead>
                    <tr><th>Data i godzina</th><th>Klient</th><th>Stolik</th><th>Liczba osób</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <?php $date = new DateTimeImmutable($reservation['date_of_reservation'], new DateTimeZone('Europe/Warsaw')); ?>
                        <tr>
                            <td><?= $escape($date->format('d.m.Y, H:i')) ?></td>
                            <td><?= $escape($reservation['email']) ?></td>
                            <td><?= $escape($reservation['table_name']) ?> · <?= $escape($reservation['location']) ?></td>
                            <td><?= $reservation['number_of_people'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/templates/footer.html'; ?>
<script src="/js/color_theme.js"></script>
</body>
</html>
