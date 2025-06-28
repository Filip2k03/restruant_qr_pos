<?php
// We need access to functions and the database on all admin pages.
// Using realpath for robust file inclusion from a subdirectory.
require_once(realpath(dirname(__FILE__) . '/../../config/functions.php'));

// Secure all admin pages.
require_admin();

// Get the current script's filename to highlight the active link
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Admin Panel'; ?> - <?php echo SITE_NAME; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Custom scrollbar for a cleaner look in Webkit browsers */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #555; }
    </style>
</head>
<body class="bg-gray-100">

<div class="flex h-screen bg-gray-100" x-data="{ sidebarOpen: false }">
    <!-- Sidebar -->
    <aside 
        class="fixed inset-y-0 left-0 z-30 w-64 bg-gray-800 text-white transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
        :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
    >
        <div class="p-4 flex items-center justify-between">
            <a href="index.php" class="text-2xl font-bold">Admin Panel</a>
            <button class="lg:hidden text-white" @click="sidebarOpen = false">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <nav class="mt-8">
            <a href="index.php" class="flex items-center px-4 py-3 transition-colors duration-200 <?php echo $current_page === 'index.php' ? 'bg-gray-700' : 'hover:bg-gray-700'; ?>">
                <i class="fas fa-tachometer-alt w-5 text-center"></i>
                <span class="mx-4">Dashboard</span>
            </a>
            <a href="menu.php" class="flex items-center px-4 py-3 mt-2 transition-colors duration-200 <?php echo $current_page === 'menu.php' ? 'bg-gray-700' : 'hover:bg-gray-700'; ?>">
                <i class="fas fa-utensils w-5 text-center"></i>
                <span class="mx-4">Menu & Categories</span>
            </a>
            <a href="orders.php" class="flex items-center px-4 py-3 mt-2 transition-colors duration-200 <?php echo $current_page === 'orders.php' ? 'bg-gray-700' : 'hover:bg-gray-700'; ?>">
                <i class="fas fa-receipt w-5 text-center"></i>
                <span class="mx-4">Orders</span>
            </a>
            <a href="tables.php" class="flex items-center px-4 py-3 mt-2 transition-colors duration-200 <?php echo $current_page === 'tables.php' ? 'bg-gray-700' : 'hover:bg-gray-700'; ?>">
                <i class="fas fa-qrcode w-5 text-center"></i>
                <span class="mx-4">Tables & QR</span>
            </a>
            <a href="coupons.php" class="flex items-center px-4 py-3 mt-2 transition-colors duration-200 <?php echo $current_page === 'coupons.php' ? 'bg-gray-700' : 'hover:bg-gray-700'; ?>">
                <i class="fas fa-tags w-5 text-center"></i>
                <span class="mx-4">Coupons</span>
            </a>
             <a href="pos.php" class="flex items-center px-4 py-3 mt-2 transition-colors duration-200 <?php echo $current_page === 'pos.php' ? 'bg-gray-700' : 'hover:bg-gray-700'; ?>">
                <i class="fas fa-cash-register w-5 text-center"></i>
                <span class="mx-4">Mini POS</span>
            </a>
        </nav>
        <div class="absolute bottom-0 w-full">
             <a href="<?php echo BASE_URL; ?>/index.php" target="_blank" class="flex items-center px-4 py-3 mt-2 text-gray-400 hover:text-white transition-colors duration-200">
                <i class="fas fa-external-link-alt w-5 text-center"></i>
                <span class="mx-4">View Public Site</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/logout.php" class="flex items-center px-4 py-3 mt-2 text-gray-400 hover:text-white transition-colors duration-200">
                <i class="fas fa-sign-out-alt w-5 text-center"></i>
                <span class="mx-4">Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="flex items-center justify-between p-4 bg-white border-b">
            <div>
                <button class="text-gray-500 focus:outline-none lg:hidden" @click="sidebarOpen = true">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="text-sm">
                Welcome, <span class="font-semibold"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>!
            </div>
        </header>

    </body>
    <html>