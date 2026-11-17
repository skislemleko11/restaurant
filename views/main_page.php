<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smaki Miasta - Restauracja</title>
    <link rel="stylesheet" href="/css/style.css">
    <meta name="description"
          content="Smaki Miasta to restauracja oferująca najlepsze dania ze świeżych składników. Sprawdź nasze menu i zarezerwuj stolik online.">
    <meta name="keywords"
          content="restauracja, jedzenie, obiad, kolacja, rezerwacja stolika, menu, Gliwice">
    <meta name="robots" content="all">
    <link rel="icon" type="image/x-icon" href="/imgs/logo.ico">
</head>
<body>
<?php require __DIR__ . '/templates/header.php'; ?>

<main class="home_main">
    <section class="hero_banner" style="background-image: url('/imgs/hero.jpg');">
        <div class="hero_overlay">
            <p class="hero_keywords">Restauracja &bull; Smak &bull; Atmosfera &bull; Wspomnienia</p>
            <h1>Smaki Miasta</h1>
            <div class="hero_actions">
                <a href="/reservation" class="btn_primary">Zarezerwuj stolik</a>
                <a href="/menu" class="btn_ghost">Zobacz menu</a>
            </div>
        </div>
    </section>

    <!-- 2. Ciepłe powitanie (styl Umami) -->
    <section class="welcome_section">
        <h2>Dzień dobry!</h2>
        <p class="welcome_lead">Miło nam, że zaglądasz na naszą stronę!</p>
        <p>
            Oto kilka słów o nas. Uwielbiamy gościć! Łączymy jakość z bezpretensjonalnością &ndash;
            komfort naszych gości, pyszna kuchnia i przyjemnie spędzony czas to dla nas najważniejsze.
            Wypiekamy własne pieczywo, przygotowujemy sosy od podstaw i pracujemy na sezonowych produktach.
        </p>
    </section>

    <!-- 3. Naprzemienne sekcje zdjęcie + opis -->
    <section class="split_section">
        <div class="split_image">
            <img src="/imgs/menu-dish.png" alt="Autorskie danie w restauracji Smaki Miasta" loading="lazy">
        </div>
        <div class="split_text">
            <span class="section_tag">Menu</span>
            <h2>Smak, który zostaje</h2>
            <p>
                Wierzymy, że jedzenie to nie tylko posiłek &ndash; to doświadczenie. Nasze dania tworzymy
                z sezonowych produktów, własnych przetworów i pełnego zaangażowania. Codziennie i z pasją.
            </p>
            <a href="/menu" class="btn_primary">Zobacz menu</a>
        </div>
    </section>

    <section class="split_section split_reverse">
        <div class="split_image">
            <img src="/imgs/table.jpg" alt="Przygotowany stolik dla gości" loading="lazy">
        </div>
        <div class="split_text">
            <span class="section_tag">Rezerwacja stolika</span>
            <h2>Celebruj wyjątkowe chwile</h2>
            <p>
                Rodzinny obiad, rocznica czy spontaniczna kolacja we dwoje &ndash; każda okazja zasługuje
                na dobrą oprawę. Wybierz dogodny termin online, a my zajmiemy się resztą.
            </p>
            <a href="/reservation" class="btn_primary">Zarezerwuj stolik</a>
        </div>
    </section>

    <section class="split_section">
        <div class="split_image">
            <img src="/imgs/events.jpg" alt="Przyjęcia okolicznościowe w Smakach Miasta" loading="lazy">
        </div>
        <div class="split_text">
            <span class="section_tag">Przyjęcia okolicznościowe</span>
            <h2>Wyjątkowe miejsce na ważne spotkania</h2>
            <p>
                Urodziny, chrzciny, komunie czy kameralne spotkania firmowe w Gliwicach.
                Zadbamy o autorskie menu dopasowane do Twoich gości i spokojną, przyjazną atmosferę.
            </p>
            <a href="/contact" class="btn_primary">Zapytaj o termin</a>
        </div>
    </section>
</main>

<?php require __DIR__ . '/templates/footer.html'; ?>
<script src="/js/color_theme.js"></script>
</body>
</html>