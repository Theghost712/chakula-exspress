    </main>

    <!-- Footer Section -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Company Info -->
                <div class="footer-section">
                    <h3><?php echo SITE_NAME; ?></h3>
                    <p>Chakula bora kwa mlango wako. Uwasilishaji wa haraka na wa uhakika katika miji yote ya Tanzania.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-section">
                    <h3>Viungo vya Haraka</h3>
                    <ul class="footer-links">
                        <li><a href="index.php">Nyumbani</a></li>
                        <li><a href="restaurants.php">Mikahawa</a></li>
                        <li><a href="about.php">Kuhusu Sisi</a></li>
                        <li><a href="contact.php">Mawasiliano</a></li>
                        <li><a href="careers.php">Ajira</a></li>
                    </ul>
                </div>

                <!-- Help & Support -->
                <div class="footer-section">
                    <h3>Usaidizi</h3>
                    <ul class="footer-links">
                        <li><a href="faq.php">Maswali Yanayoulizwa Mara kwa Mara</a></li>
                        <li><a href="terms.php">Masharti na Vigezo</a></li>
                        <li><a href="privacy.php">Sera ya Faragha</a></li>
                        <li><a href="refund.php">Sera ya Rudi Fedha</a></li>
                        <li><a href="security.php">Usalama</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="footer-section">
                    <h3>Wasiliana Nasi</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-map-marker-alt"></i> Dar es Salaam, Tanzania</li>
                        <li><i class="fas fa-phone"></i> +255 787 654 321</li>
                        <li><i class="fas fa-envelope"></i> info@chakulaexpress.co.tz</li>
                        <li><i class="fab fa-whatsapp"></i> +255 712 345 678</li>
                        <li><i class="fas fa-clock"></i> 08:00 - 22:00 kila siku</li>
                    </ul>
                </div>
            </div>

            <!-- Download Apps -->
            <div class="app-download" style="text-align: center; margin: 2rem 0; padding: 2rem; background: rgba(255,255,255,0.1); border-radius: 10px;">
                <h3 style="color: white; margin-bottom: 1rem;">Pakua Programu Yetu</h3>
                <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                    <a href="#" style="display: inline-flex; align-items: center; gap: 10px; background: black; color: white; padding: 0.75rem 1.5rem; border-radius: 5px; text-decoration: none;">
                        <i class="fab fa-apple" style="font-size: 1.5rem;"></i>
                        <div>
                            <small>Pakua kwenye</small>
                            <div>App Store</div>
                        </div>
                    </a>
                    <a href="#" style="display: inline-flex; align-items: center; gap: 10px; background: black; color: white; padding: 0.75rem 1.5rem; border-radius: 5px; text-decoration: none;">
                        <i class="fab fa-google-play" style="font-size: 1.5rem;"></i>
                        <div>
                            <small>Pakua kwenye</small>
                            <div>Google Play</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Copyright -->
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Haki zote zimehifadhiwa.</p>
                <p style="font-size: 0.8rem; margin-top: 0.5rem;">Ilitengenezwa kwa upendo na juhudi nchini Tanzania 🇹🇿</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript Files -->
    <script src="assets/js/main.js"></script>
    
    <!-- Page Specific JS -->
    <?php if (basename($_SERVER['PHP_SELF']) == 'index.php'): ?>
        <script src="assets/js/home.js"></script>
    <?php elseif (basename($_SERVER['PHP_SELF']) == 'restaurants.php'): ?>
        <script src="assets/js/restaurants.js"></script>
    <?php elseif (basename($_SERVER['PHP_SELF']) == 'menu.php'): ?>
        <script src="assets/js/menu.js"></script>
    <?php endif; ?>
    
</body>
</html>