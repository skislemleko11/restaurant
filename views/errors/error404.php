<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <title>Nie znaleziono - Error 404</title>
</head>
<body>
    <?php require __DIR__ . "/../templates/header.php"; ?>

    <main>
        <div class="main_verification">
            <h1>Błąd 404</h1>
            <p>Przepraszamy, ale strona, której szukasz, nie istnieje lub została przeniesiona.</p>
            <p><a href="/" class="error404">Wróć na stronę główną</a></p>
        </div>
    </main>

    <?php
    require __DIR__ . "/../templates/footer.html";
    ?>
    <script src="/js/color_theme.js"></script>
</body>
</html>