<?php
declare(strict_types=1);

use Velo\Session\FlashMessages\FlashMessagesInterface;

/**
 * @var FlashMessagesInterface $flashMessages
 * @var string $token
 * @var string $csrfToken
 */
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <title>Ustaw nowe hasło</title>
</head>
<body>
<?php require __DIR__ . '/templates/header.php'; ?>

<main>
    <div class="register_main">
        <h1>Ustaw nowe hasło</h1>
        <?php if ($flashMessages->has(FlashMessagesInterface::ERROR)): ?>
            <ul>
                <?php foreach ($flashMessages->get(FlashMessagesInterface::ERROR) as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post">
            <label>Nowe hasło <input type="password" name="password" minlength="8" required></label>
            <label>Potwierdź hasło <input type="password" name="password_confirmation" minlength="8" required></label>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <input type="submit" value="Zmień hasło">
        </form>
    </div>
</main>

<?php require __DIR__ . '/templates/footer.html'; ?>
<script src="/js/color_theme.js"></script>
</body>
</html>
