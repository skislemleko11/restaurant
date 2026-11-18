<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smaki Miasta - Restauracja</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="description"
          content="Smaki Miasta to restauracja oferująca najlepsze dania ze świeżych składników. Sprawdź nasze menu i zarezerwuj stolik online.">
    <meta name="keywords"
          content="restauracja, jedzenie, obiad, kolacja, rezerwacja stolika, menu">
    <meta name="robots" content="all">
    <link rel="icon" type="image/x-icon" href="imgs/logo.ico">
</head>
<body>
<?php require __DIR__ . '/templates/header.php'; ?>

<main class="menu_main">
    <h1>Nasze Menu</h1>

    <?php $cacheKey = $menuModifiedAt === null ? '0' : str_replace(['-', ':', ' '], '', $menuModifiedAt); ?>
    <a href="/menu/pdf?v=<?= urlencode($cacheKey) ?>" target="_blank" class="download_menu_btn">
        Otwórz PDF w nowej karcie / Pobierz
    </a>

    <div class="pdf_frame_wrapper">
        <iframe src="/menu/pdf?v=<?= urlencode($cacheKey) ?>#view=FitH&navpanes=0" title="Menu restauracji Smaki Miasta"></iframe>
    </div>
</main>

<?php require __DIR__ . '/templates/footer.html'; ?>
<script src="/js/color_theme.js"></script>
</body>
</html>