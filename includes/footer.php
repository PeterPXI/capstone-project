</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <a class="footer-brand" href="<?= $assetPrefix ?>index.php">Deal<span>Exit</span></a>
        <p class="footer-copy">&copy; <?= date('Y') ?> DealExit — مساحة تجمع أصحاب المشاريع والمشترين.</p>
    </div>
</footer>
<script type="module" src="<?= $assetPrefix ?>assets/css/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('[data-bs-toggle="drawer"]').forEach(function (toggle) {
    const drawerId = toggle.getAttribute('data-bs-target');
    const drawer = drawerId ? document.querySelector(drawerId) : null;
    if (!drawer) return;

    toggle.addEventListener('click', function () {
        requestAnimationFrame(function () {
            toggle.setAttribute('aria-expanded', String(Boolean(drawer.open)));
        });
    });

    drawer.addEventListener('close', function () {
        toggle.setAttribute('aria-expanded', 'false');
    });
});
</script>
</body>
</html>
