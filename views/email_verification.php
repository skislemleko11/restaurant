<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Weryfikacja adresu email</title>
    <link rel="stylesheet" href="/css/style.css">

</head>
<body>
<?php
    require __DIR__ . "/templates/header.html";
?>
<main>
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
</body>
</html>
