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
    <title>Rejestracja</title>
</head>
<body>
<h1>Rejestracja</h1>

<?php if (($flashMessages->has(FlashMessagesInterface::ERROR))): ?>
    <ul>
        <?php foreach ($flashMessages->get(FlashMessagesInterface::ERROR) as $error): ?>
            <li><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if (($success = $session->getFlash('success')) !== null): ?>
    <p><?= $success ?></p>
<?php endif; ?>

<form method="post">
    <label>Email <input type="email" name="email"
                        value="<?= htmlspecialchars($session->getFlash('email', ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                        required></label>
    <label>Hasło <input type="password" name="password" minlength="8" required></label>
    <label>Potwierdź hasło <input type="password" name="password_confirmation" minlength="8" required></label>
    <input type="hidden" value="<?= $token ?>" name="csrf_token">
    <input type="submit" value="Zarejestruj">
</form>
</body>
</html>