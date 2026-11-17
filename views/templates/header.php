<?php
/** @var SessionInterface $session */
use Velo\Session\Session\SessionInterface;
?>
<header>
    <nav>
        <a href="/" class="nav_logo_link">
            <img class="logo" src="/imgs/logo.png" alt="Logo restauracji Smaki Miasta">
        </a>
        <ul class="nav_links">
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
                        <form method="post" action="/logout">
                            <input type="hidden" name="csrf_token"
                                   value="<?= htmlspecialchars((string)$session->get('csrf_token', ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                            <button type="submit" class="logout-button">Wyloguj</button>
                        </form>
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