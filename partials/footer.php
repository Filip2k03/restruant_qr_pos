    </main>
    <!-- End of main content -->

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-16">
        <div class="container mx-auto px-6 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="font-bold text-xl font-display mb-4">Cozy Corner</h3>
                    <p class="text-gray-400">Your favorite local cafe for great coffee, delicious food, and a warm, welcoming atmosphere. Est. 2024.</p>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="<?php echo BASE_URL; ?>/index.php" class="text-gray-400 hover:text-white transition">Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/menu.php" class="text-gray-400 hover:text-white transition">Full Menu</a></li>
                         <?php if (is_logged_in()): ?>
                           <li><a href="<?php echo BASE_URL; ?>/my_orders.php" class="text-gray-400 hover:text-white transition">My Orders</a></li>
                         <?php else: ?>
                           <li><a href="<?php echo BASE_URL; ?>/login.php" class="text-gray-400 hover:text-white transition">My Account</a></li>
                         <?php endif; ?>
                    </ul>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-4">Visit Us</h3>
                    <p class="text-gray-400">123 Cafe Lane, Mandalay</p>
                    <p class="text-gray-400">info@cozycorner.com</p>
                    <p class="text-gray-400">+95 9 123 456 789</p>
                </div>
            </div>
            <div class="mt-12 border-t border-gray-700 pt-8 text-center text-gray-500">
                <p>&copy; <?php echo date('Y'); ?> Cozy Corner Cafe. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</div> <!-- Closing div for x-data -->

</body>
</html>
