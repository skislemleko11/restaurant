<?php
declare(strict_types=1);

/**
 * @var string $token
 * @var list<string> $errors
 * @var list<string> $successMessages
 * @var string $minimumDate
 * @var string $initialDate
 * @var string $maximumDate
 * @var int $maximumPartySize
 */
$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rezerwacja stolika</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="/js/reservation.js" defer></script>
    <link rel="icon" type="image/x-icon" href="/imgs/logo.ico">
</head>
<body>
<?php require __DIR__ . '/templates/header.php'; ?>
<main class="reservation_main">
    <h1>Rezerwacja stolika</h1>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error" role="alert"><?= $escape($error) ?></div>
    <?php endforeach; ?>
    <?php foreach ($successMessages as $message): ?>
        <div class="alert alert-success" role="status"><?= $escape($message) ?></div>
    <?php endforeach; ?>

    <section class="reservation_card">
        <h2>Wybierz termin</h2>
        <div class="reservation_row">
            <label>Data
                <input id="reservation-date" type="date"
                       value="<?= $escape($initialDate) ?>" min="<?= $escape($minimumDate) ?>"
                       max="<?= $escape($maximumDate) ?>" required>
            </label>
            <label>Liczba osób
                <input id="reservation-people" type="number" min="1"
                       max="<?= $maximumPartySize ?>" value="2" required>
            </label>
        </div>
    </section>

    <section class="reservation_section">
        <h2>Dostępne godziny</h2>
        <div id="reservation-status" class="reservation_status"></div>
        <div id="reservation-slots" class="reservation_buttons_grid"></div>
    </section>

    <section class="reservation_section">
        <h2>Wybierz stolik</h2>
        <div id="reservation-tables" class="reservation_buttons_grid"></div>
    </section>

    <form id="reservation-form" method="post" action="/reservation">
        <input type="hidden" name="csrf_token" value="<?= $escape($token) ?>">
        <input type="hidden" name="date" id="selected-date">
        <input type="hidden" name="time" id="selected-time">
        <input type="hidden" name="people" id="selected-people">
        <input type="hidden" name="table_id" id="selected-table">
        <button id="reservation-submit" type="submit" disabled>
            Zarezerwuj stolik
        </button>
    </form>
</main>
<?php require __DIR__ . '/templates/footer.html'; ?>
<script src="/js/color_theme.js"></script>
</body>
</html>