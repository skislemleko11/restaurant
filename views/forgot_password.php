<?php
declare(strict_types=1);

use Velo\Session\FlashMessages\FlashMessagesInterface;
use Velo\Session\Session\SessionInterface;

/**
 * @var FlashMessagesInterface $flashMessages
 * @var SessionInterface $session
 * @var string $token
 */
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <title>Reset hasła</title>
</head>
<body>
<?php require __DIR__ . '/templates/header.php'; ?>

<main>
    <div class="register_main">
        <h1>Reset hasła</h1>
        <?php if ($flashMessages->has(FlashMessagesInterface::ERROR)): ?>
            <ul>
                <?php foreach ($flashMessages->get(FlashMessagesInterface::ERROR) as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if (($success = $session->getFlash('success')) !== null): ?>
            <p><?= htmlspecialchars($success, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        <?php endif; ?>

        <form method="post">
            <label>Email <input type="email" name="email" required></label>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <input type="submit" value="Wyślij link">
        </form>
    </div>
</main>

<?php require __DIR__ . '/templates/footer.html'; ?>
<script src="/js/color_theme.js"></script>
</body>
</html>
