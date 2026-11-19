<?php
/** @var SessionInterface $session */

use Velo\Session\Session\SessionInterface;

?>
<header>
    <a class="skip_link" href="#main-content">Przejdź do treści</a>
    <nav id="main-navigation" aria-label="Główna nawigacja">
        <a href="/" class="nav_logo_link">
            <img class="logo" src="/imgs/logo.png" alt="Logo restauracji Smaki Miasta">
        </a>
        <input class="nav_checkbox" id="nav-toggle" type="checkbox">
        <label class="nav_toggle" for="nav-toggle" aria-label="Otwórz lub zamknij menu">
            <img src="/imgs/hamburger.png" alt="" width="24" height="24">
        </label>
        <ul class="nav_links" id="nav-links">
                <li class="theme-button"><img src="/imgs/theme.png" alt="Zmień motyw" class="theme"></li>
                <li><a href="/">Strona główna</a></li>
                <li><a href="/menu">Menu</a></li>
                <li><a href="/reservation">Rezerwacja</a></li>
                <li><a href="/contact">Kontakt</a></li>
                <?php if ($session->has('user_id')): ?>
                    <li>
                        <details class="account-menu">
                            <summary aria-label="Konto">
                                <img src="/imgs/register.png" alt="Konto" class="register-icon">
                            </summary>
                            <div class="account-menu-panel">
                                <form method="post" action="/logout">
                                    <input type="hidden" name="csrf_token"
                                           value="<?= htmlspecialchars((string)$session->get('csrf_token', ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                                    <button type="submit" class="logout-button">Wyloguj</button>
                                </form>
                                <div class="account-menu-links" id="admin-menu-links" hidden>
                                    <a href="/admin/menu">Zarządzanie menu</a>
                                    <a href="/admin/schedule">Kalendarz dostępności</a>
                                    <a href="/admin/tables">Zarządzanie stolikami</a>
                                </div>
                            </div>
                        </details>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="/login">
                            <img src="/imgs/register.png" alt="Konto" class="register-icon">
                        </a>
                    </li>
                <?php endif; ?>
        </ul>
    </nav>
</header>
    <script src="/js/admin_menu_visibility.js"></script>