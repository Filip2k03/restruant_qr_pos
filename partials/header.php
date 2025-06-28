<?php
// We need to access the session and functions on every page.
// The db.php file is already included within functions.php
require_once(realpath(dirname(__FILE__) . '/../config/functions.php'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- The title will be set dynamically on each page -->
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Playfair Display', serif; }
        .smooth-scroll { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <div x-data="{ mobileMenuOpen: false }">
        <header class="bg-white/90 backdrop-blur-lg shadow-sm sticky top-0 w-full z-50">
            <div class="container mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="text-2xl font-bold font-display text-gray-900">
                        <a href="<?php echo BASE_URL; ?>/index.php">Cozy Corner</a>
                    </div>

                    <!-- Desktop Navigation -->
                    <nav class="hidden md:flex items-center space-x-6">
                        <a href="<?php echo BASE_URL; ?>index.php" class="text-gray-600 hover:text-amber-600 transition">Home</a>
                        <a href="<?php echo BASE_URL; ?>menu.php" class="text-gray-600 hover:text-amber-600 transition">Menu</a>
                        <?php if (is_logged_in()): ?>
                            <a href="<?php echo BASE_URL; ?>my_orders.php" class="text-gray-600 hover:text-amber-600 transition">My Orders</a>
                        <?php endif; ?>
                        <?php if (is_admin()): ?>
                            <a href="<?php echo BASE_URL; ?>admin/index.php" class="text-gray-600 hover:text-amber-600 transition">Admin Panel</a>
                        <?php endif; ?>
                    </nav>

                    <!-- Auth & Cart Buttons -->
                    <div class="hidden md:flex items-center space-x-4">
                        <?php if (is_logged_in()): ?>
                            <a href="<?php echo BASE_URL; ?>/cart.php" class="relative text-gray-600 hover:text-amber-600 transition">
                                <i class="fa-solid fa-cart-shopping text-xl"></i>
                                <?php $cart_count = get_cart_item_count($conn); ?>
                                <?php if ($cart_count > 0): ?>
                                    <span class="absolute -top-2 -right-3 bg-amber-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"><?php echo $cart_count; ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="<?php echo BASE_URL; ?>/logout.php" class="px-4 py-2 text-sm font-medium text-amber-600 border border-amber-500 rounded-full hover:bg-amber-500 hover:text-white transition">Log Out</a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>/login.php" class="px-4 py-2 text-sm font-medium text-amber-600 border border-amber-500 rounded-full hover:bg-amber-500 hover:text-white transition">Log In</a>
                            <a href="<?php echo BASE_URL; ?>/register.php" class="px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-full hover:bg-amber-700 transition">Sign Up</a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Mobile Menu Button -->
                    <div class="md:hidden flex items-center space-x-4">
                         <?php if (is_logged_in()): ?>
                            <a href="<?php echo BASE_URL; ?>cart.php" class="relative text-gray-600 hover:text-amber-600 transition">
                                <i class="fa-solid fa-cart-shopping text-xl"></i>
                                <?php if ($cart_count > 0): ?>
                                    <span class="absolute -top-2 -right-3 bg-amber-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"><?php echo $cart_count; ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-800 focus:outline-none">
                            <i :class="mobileMenuOpen ? 'fa-solid fa-times' : 'fa-solid fa-bars'" class="w-6 h-6 text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Mobile Menu -->
            <div x-show="mobileMenuOpen" x-transition class="md:hidden bg-white border-t">
                <nav class="flex flex-col space-y-2 px-6 py-4">
                    <a href="<?php echo BASE_URL; ?>/index.php" class="block py-2 text-gray-600 hover:text-amber-600">Home</a>
                    <a href="<?php echo BASE_URL; ?>/menu.php" class="block py-2 text-gray-600 hover:text-amber-600">Menu</a>
                     <?php if (is_logged_in()): ?>
                        <a href="<?php echo BASE_URL; ?>/my_orders.php" class="block py-2 text-gray-600 hover:text-amber-600">My Orders</a>
                    <?php endif; ?>
                    <?php if (is_admin()): ?>
                        <a href="<?php echo BASE_URL; ?>/admin/index.php" class="block py-2 text-gray-600 hover:text-amber-600">Admin Panel</a>
                    <?php endif; ?>
                    
                    <div class="border-t border-gray-200 pt-4 space-y-3">
                         <?php if (is_logged_in()): ?>
                            <a href="<?php echo BASE_URL; ?>/logout.php" class="w-full text-center block px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-full hover:bg-amber-700">Log Out</a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>/login.php" class="w-full text-center block px-4 py-2 text-sm font-medium text-amber-600 border border-amber-500 rounded-full hover:bg-amber-500 hover:text-white">Log In</a>
                            <a href="<?php echo BASE_URL; ?>/register.php" class="w-full text-center block px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-full hover:bg-amber-700">Sign Up</a>
                        <?php endif; ?>
                    </div>
                </nav>
            </div>
        </header>

</body>
</html>