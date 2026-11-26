<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Weryfikacja adresu email</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="icon" type="image/x-icon" href="/imgs/logo.ico">
</head>
<body>
<?php
    require __DIR__ . "/templates/header.php";
?>
<main id="main-content">
    <div class="main_verification">
        <h1><?= /** @var bool $success */
            $success ? 'Email zweryfikowany' : 'Weryfikacja nieudana' ?></h1>
        <p><?= /** @var string $message */
            htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    </div>
</main>
<?php
    require __DIR__ . "/templates/footer.html";
?>
<script src="/js/color_theme.js"></script>
</body>
</html>
