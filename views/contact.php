<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontakt - Smaki Miasta</title>
    <link rel="stylesheet" href="/css/style.css">
    <meta name="description"
          content="Skontaktuj się z restauracją Smaki Miasta. Sprawdź adres, numer telefonu, godziny otwarcia oraz dojazd.">
    <meta name="keywords"
          content="restauracja, kontakt, adres, telefon, godziny otwarcia, Smaki Miasta">
    <meta name="robots" content="all">
    <link rel="icon" type="image/x-icon" href="/imgs/logo.ico">
</head>
<body>
<?php require __DIR__ . '/templates/header.php'; ?>

<main class="contact_main" id="main-content">
    <h1>Kontakt</h1>

    <div class="contact_grid">
        <section class="contact_card">
            <h2>Dane kontaktowe</h2>
            <ul class="contact_list">
                <li>
                    <strong>Adres:</strong>
                    <span>ul. Przykładowa 10, Miasto</span>
                </li>
                <li>
                    <strong>Telefon:</strong>
                    <a href="tel:+48123456789">+48 123 456 789</a>
                </li>
                <li>
                    <strong>E-mail:</strong>
                    <a href="mailto:kontakt@smakimiasta.pl">kontakt@smakimiasta.pl</a>
                </li>
            </ul>

            <a href="/reservation" class="contact_cta_btn">Zarezerwuj stolik online</a>
        </section>

        <section class="contact_card">
            <h2>Godziny otwarcia</h2>
            <ul class="hours_list">
                <li>
                    <span>Poniedziałek – Czwartek</span>
                    <strong>12:00 – 22:00</strong>
                </li>
                <li>
                    <span>Piątek – Sobota</span>
                    <strong>12:00 – 23:00</strong>
                </li>
                <li>
                    <span>Niedziela</span>
                    <strong>12:00 – 21:00</strong>
                </li>
            </ul>
            <p class="contact_note">
                W sprawie imprez okolicznościowych i rezerwacji grupowych prosimy o kontakt telefoniczny lub mailowy.
            </p>
        </section>
    </div>

    <section class="contact_card contact_map_card">
        <h2>Jak do nas trafić</h2>
        <div class="contact_map">
            <iframe
                src="https://maps.google.com/maps?q=Chorzowska%205,%20Gliwice&z=16&output=embed"
                title="Mapa dojazdu - Chorzowska 5, Gliwice"
                loading="lazy">
            </iframe>
        </div>
    </section>
</main>

<?php require __DIR__ . '/templates/footer.html'; ?>
<script src="/js/color_theme.js"></script>
</body>
</html>