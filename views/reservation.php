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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="/js/reservation.js" defer></script>
</head>
<body>
<main class="container py-4">
    <h1 class="mb-4">Rezerwacja stolika</h1>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger" role="alert"><?= $escape($error) ?></div>
    <?php endforeach; ?>
    <?php foreach ($successMessages as $message): ?>
        <div class="alert alert-success" role="status"><?= $escape($message) ?></div>
    <?php endforeach; ?>

    <section class="card mb-4">
        <div class="card-body">
            <h2 class="h5 card-title">Wybierz termin</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="reservation-date">Data</label>
                    <input class="form-control" id="reservation-date" type="date"
                           value="<?= $escape($initialDate) ?>" min="<?= $escape($minimumDate) ?>"
                           max="<?= $escape($maximumDate) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="reservation-people">Liczba osób</label>
                    <input class="form-control" id="reservation-people" type="number" min="1"
                           max="<?= $maximumPartySize ?>" value="2" required>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4" aria-labelledby="slots-heading">
        <h2 class="h5" id="slots-heading">Dostępne godziny</h2>
        <div id="reservation-status" class="text-body-secondary mb-2" role="status" aria-live="polite"></div>
        <div id="reservation-slots" class="d-flex flex-wrap gap-2"></div>
    </section>

    <section class="mb-4" aria-labelledby="tables-heading">
        <h2 class="h5" id="tables-heading">Wybierz stolik</h2>
        <div id="reservation-tables" class="d-flex flex-wrap gap-2"></div>
    </section>

    <form id="reservation-form" method="post" action="/reservation">
        <input type="hidden" name="csrf_token" value="<?= $escape($token) ?>">
        <input type="hidden" name="date" id="selected-date">
        <input type="hidden" name="time" id="selected-time">
        <input type="hidden" name="people" id="selected-people">
        <input type="hidden" name="table_id" id="selected-table">
        <button class="btn btn-primary" id="reservation-submit" type="submit" disabled>
            Zarezerwuj stolik
        </button>
    </form>
</main>
</body>
</html>
