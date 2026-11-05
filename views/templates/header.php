<header>
    <nav>
        <ul class="nav_links">
            <li class="nav_item"><a href="/"><img class="logo" src="/imgs/logo.png" alt="Logo restauracji Smaki Miasta"></a>
            </li>
            <div class="separator">
                <!--TODO: PLS YURII FIX THIS, IT'S SEMANTICLY INCORRECT -->
                <li><a href="/">Strona główna</a></li>
                <li><a href="/menu">Menu</a></li>
                <li><a href="/reservation">Rezerwacja</a></li>
                <li><a href="/contact">Kontakt</a></li>
                <?php /** @var SessionInterface $session */

                use Velo\Session\Session\SessionInterface;

                if ($session->has('user_id')): ?>
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
                <?php else: ?>
                    <li><a href="/login"><img src="/imgs/register.png" alt="Konto" class="register-icon"></a></li>
                <?php endif; ?>
            </div>
        </ul>
    </nav>
</header>