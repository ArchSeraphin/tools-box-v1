</main>
    <?php if (isLoggedIn() && (!isset($force_hide_nav) || !$force_hide_nav)): ?>
        </div> <!-- End of flex container for sidebar layout -->
    <?php else: ?>
    <!-- Public footer only for non-logged in pages -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500">&copy; <?php echo date('Y'); ?> Voilà Voilà Hub. Tous droits réservés.</p>
        </div>
    </footer>
    <?php endif; ?>
</body>
</html>