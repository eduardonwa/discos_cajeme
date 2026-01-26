<footer class="site-footer no-print" role="contentinfo">
    <div class="site-footer__top">
        <nav class="footer-links" aria-label="Enlaces del sitio">
            <section class="footer-links__group">
                <h2 class="footer-title">Tienda</h2>
                <ul class="no-list-style">
                    <li><a href="#">Lorem ipsum al</a></li>
                    <li><a href="#">Dolar at</a></li>
                    <li><a href="#">Veraotio</a></li>
                    <li><a href="#">Chauloe</a></li>
                </ul>
            </section>

            <section class="footer-links__group">
                <h2 class="footer-title">Acerca</h2>
                <ul class="no-list-style">
                    <li><a href="#">Nosotros</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Contacto</a></li>
                    <li><a href="#">Aprender más</a></li>
                    <li><a href="#">Tiendas</a></li>
                </ul>
            </section>

            <section class="footer-links__group">
                <h2 class="footer-title">Más</h2>
                <ul class="no-list-style">
                    <li><a href="#">Dolar at</a></li>
                    <li><a href="#">Lorem at al</a></li>
                    <li><a href="#">Veraotio</a></li>
                </ul>
            </section>
        </nav>

        <section class="footer-brand | flow">
            <div class="brand">
                <x-application-mark />
                <p class="brand__tagline">Explora nuestro innovador catálogo</p>
            </div>

            <form class="newsletter" action="" method="POST">
                <label for="sr-only" for="news-email" class="sr-only">Tu Email</label>
                <input id="news-email" type="email" placeholder="Tu email..." required>
                <button type="submit" class="button" data-type="newsletter">Suscribirme</button>
            </form>
        </section>
    </div>

    <hr aria-hidden="true">

    <div class="site-footer__bottom">
        <p class="legal-copy">WEBSHOP · TODOS LOS DERECHOS RESERVADOS</p>

        <nav class="legal-links" aria-label="Legales">
            <ul class="no-list-style">
                <li>
                    <a href="#">Política de privacidad</a>
                </li>
                <li>
                    <a href="#">Términos de uso</a>
                </li>
                <li>
                    <a href="#">Contáctanos</a>
                </li>
            </ul>
        </nav>

        <nav class="social" aria-label="Redes sociales">
            <ul class="no-list-style">
                <li>
                    <x-icon href="https://facebook.com">
                        <x-ui.icons.socials.facebook />
                    </x-icon>
                </li>
                <li>
                    <x-icon href="https://instagram.com">
                        <x-ui.icons.socials.instagram />
                    </x-icon>
                </li>
                <li>
                    <x-icon href="https://twitter.com">
                        <x-ui.icons.socials.twitter />
                    </x-icon>
                </li>
                <li>
                    <x-icon href="https://youtube.com">
                        <x-ui.icons.socials.youtube />
                    </x-icon>
                </li>
            </ul>
        </nav>
    </div>
</footer>