</main>
        <footer class="footer">
            <p>&copy; <?php echo date("Y"); ?> Modern POS. All Rights Reserved.</p>
        </footer>
    </div> <script>
        document.querySelectorAll('.sidebar-menu .has-submenu > a').forEach(function(menu) {
            menu.addEventListener('click', function(e) {
                e.preventDefault();
                let parent = this.parentElement;
                if (parent.classList.contains('open')) {
                    parent.classList.remove('open');
                } else {
                    // Optional: close other open submenus
                    document.querySelectorAll('.sidebar-menu .has-submenu.open').forEach(function(openMenu) {
                        openMenu.classList.remove('open');
                    });
                    parent.classList.add('open');
                }
            });
        });
    </script>

    <?php if (isset($page_scripts) && is_array($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?php echo htmlspecialchars($script); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>