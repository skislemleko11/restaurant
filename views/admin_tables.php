<?php
declare(strict_types=1);

/**
 * @var string $token
 * @var list<array{id: int, name: string, location: string, capacity: int, is_active: bool}> $tables
 * @var list<string> $errors
 * @var list<string> $successMessages
 */
$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stoliki - restauracja</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<?php require __DIR__ . '/templates/header.php'; ?>
<main class="admin_schedule_main">
    <h1>Zarządzanie stolikami</h1>
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error" role="alert"><?= $escape($error) ?></div>
    <?php endforeach; ?>
    <?php foreach ($successMessages as $message): ?>
        <div class="alert alert-success" role="status"><?= $escape($message) ?></div>
    <?php endforeach; ?>

    <section class="schedule_card">
        <h2>Dodaj stolik</h2>
        <form method="post" action="/admin/tables" class="schedule_form">
            <input type="hidden" name="csrf_token" value="<?= $escape($token) ?>">
            <label>Nazwa / numer
                <input type="text" name="name" maxlength="255" required>
            </label>
            <label>Miejsce
                <input type="text" name="location" maxlength="255" placeholder="np. Sala główna">
            </label>
            <label>Pojemność
                <input type="number" name="capacity" min="1" max="12" required>
            </label>
            <label><input type="checkbox" name="is_active" value="1" checked> Aktywny</label>
            <button type="submit">Dodaj stolik</button>
        </form>
    </section>

    <section class="schedule_card">
        <h2>Lista stolików</h2>
        <label>Filtruj według pojemności
            <select id="table-capacity-filter">
                <option value="">Wszystkie</option>
                <?php for ($capacity = 1; $capacity <= 12; $capacity++): ?>
                    <option value="<?= $capacity ?>"><?= $capacity ?> miejsc<?= $capacity === 1 ? 'e' : 'a' ?></option>
                <?php endfor; ?>
            </select>
        </label>
        <?php if ($tables === []): ?>
            <p>Brak stolików.</p>
        <?php else: ?>
            <div class="table_management_list">
                <?php foreach ($tables as $table): ?>
                    <article class="table_management_item" data-capacity="<?= $table['capacity'] ?>">
                        <div class="blocked_period_heading">
                            <strong><?= $escape($table['name']) ?></strong>
                            <span class="blocked_badge"><?= $table['is_active'] ? 'AKTYWNY' : 'NIEAKTYWNY' ?></span>
                        </div>
                        <p><?= $escape($table['location'] !== '' ? $table['location'] : 'Brak miejsca') ?>
                            · <?= $table['capacity'] ?> miejsc</p>
                        <details>
                            <summary>Edytuj stolik</summary>
                            <form method="post" action="/admin/tables/<?= $table['id'] ?>/update" class="schedule_form">
                                <input type="hidden" name="csrf_token" value="<?= $escape($token) ?>">
                                <label>Nazwa / numer
                                    <input type="text" name="name" maxlength="255" value="<?= $escape($table['name']) ?>" required>
                                </label>
                                <label>Miejsce
                                    <input type="text" name="location" maxlength="255" value="<?= $escape($table['location']) ?>">
                                </label>
                                <label>Pojemność
                                    <input type="number" name="capacity" min="1" max="12" value="<?= $table['capacity'] ?>" required>
                                </label>
                                <label><input type="checkbox" name="is_active" value="1" <?= $table['is_active'] ? 'checked' : '' ?>> Aktywny</label>
                                <button type="submit">Zapisz zmiany</button>
                            </form>
                        </details>
                        <form method="post" action="/admin/tables/<?= $table['id'] ?>/delete" class="inline_form"
                              onsubmit="return confirm('Usunąć stolik?')">
                            <input type="hidden" name="csrf_token" value="<?= $escape($token) ?>">
                            <button type="submit" class="danger_button">Usuń</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/templates/footer.html'; ?>
<script>
document.getElementById('table-capacity-filter').addEventListener('change', function () {
    document.querySelectorAll('.table_management_item').forEach(function (item) {
        item.hidden = this.value !== '' && item.dataset.capacity !== this.value;
    }.bind(this));
});
</script>
<script src="/js/color_theme.js"></script>
</body>
</html>
