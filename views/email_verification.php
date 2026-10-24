<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Weryfikacja adresu email</title>
</head>
<body>
<h1><?= /** @var bool $success */
    $success ? 'Email zweryfikowany' : 'Weryfikacja nieudana' ?></h1>
<p><?= /** @var string $message */
    htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
</body>
</html>
