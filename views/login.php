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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <title>Rejestracja</title>
</head>
<body>
<?php require __DIR__ . '/templates/header.php'; ?>

<main>
    <div class="register_main">
        <h1>Zaloguj się</h1>
        <?php if (($flashMessages->has(FlashMessagesInterface::ERROR))): ?>
            <ul>
                <?php foreach ($flashMessages->get(FlashMessagesInterface::ERROR) as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post">
            <label>Email <input type="email" name="email"
                                value="<?= htmlspecialchars($session->getFlash('email', ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                required></label>
            <label>Hasło <input type="password" name="password" required></label>
            <input type="hidden" value="<?= $token ?>" name="csrf_token">
            <input type="submit" value="Zaloguj się">
        </form>

        <p><a href="/forgot-password">Nie pamiętasz hasła?</a></p>
        <p><a href="/register" class="dont_have_account">Nie masz konta? Zarejestruj się</a></p>
    </div>
</main>

<?php require __DIR__ . '/templates/footer.html'; ?>
<script src="/js/color_theme.js"></script>
</body>
</html>