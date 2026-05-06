</main>

<footer class="footer">
    <div class="footer-container">
        <div class="footer-brand">
            <a href="<?= BASE_URL ?>/index.php" class="nav-brand">
                <img src="<?= BASE_URL ?>/assets/img/logo.svg" alt="SkillSwap logo" class="brand-logo">
                <span class="brand-text">Skill<span class="brand-accent">Swap</span></span>
            </a>
            <p class="footer-tagline">Teach what you know.<br>Learn what you don't. No money needed.</p>
        </div>
        <div class="footer-links">
            <div class="footer-col">
                <h4>Platform</h4>
                <a href="<?= BASE_URL ?>/skills/explore.php">Explore Skills</a>
                <a href="<?= BASE_URL ?>/match/smart_match.php">Smart Match</a>
                <a href="<?= BASE_URL ?>/auth/register.php">Join SkillSwap</a>
            </div>
            <div class="footer-col">
                <h4>Categories</h4>
                <a href="<?= BASE_URL ?>/skills/explore.php?category=1">Tech</a>
                <a href="<?= BASE_URL ?>/skills/explore.php?category=2">Design</a>
                <a href="<?= BASE_URL ?>/skills/explore.php?category=5">Academics</a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© <?= date('Y') ?> SkillSwap · Built for WT Final Project · PHP + MySQL</p>
    </div>
</footer>

<?php if (empty($disable_legacy_js)): ?>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script src="<?= BASE_URL ?>/assets/js/location.js"></script>
<?php endif; ?>
</body>
</html>
