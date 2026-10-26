<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Coś poszło nie tak...</title>
</head>
<body>
    <?php
        require __DIR__ . "/../templates/header.html";
    ?>

    <main>
        <div class="main_verification">
            <h1>Coś poszło nie tak... Spróbuj ponownie później</h1>
            <p><a href="/" class="error404">Wróć na stronę główną</a></p>
        </div>
    </main>

    <?php
    require __DIR__ . "/../templates/footer.html";
    ?>
</body>
</html>