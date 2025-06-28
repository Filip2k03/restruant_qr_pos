<?php
// We don't need a full header here, just the config for DB connection
require_once 'config/db.php';

// Fetch a few active coupons to display on the landing page
$active_coupons = [];
$today = date('Y-m-d');
$sql_coupons = "SELECT * FROM coupons WHERE is_active = TRUE AND (expiry_date IS NULL OR expiry_date >= ?) ORDER BY created_at DESC LIMIT 3";
$stmt_coupons = $conn->prepare($sql_coupons);
$stmt_coupons->bind_param("s", $today);
$stmt_coupons->execute();
$result_coupons = $stmt_coupons->get_result();
if ($result_coupons->num_rows > 0) {
    while ($row = $result_coupons->fetch_assoc()) {
        $active_coupons[] = $row;
    }
}
$stmt_coupons->close();

// Fetch a few featured menu items to display
$featured_items = [];
$sql_items = "SELECT * FROM menu_items WHERE is_available = TRUE ORDER BY id DESC LIMIT 4";
$result_items = $conn->query($sql_items);
if($result_items && $result_items->num_rows > 0) {
    while($row = $result_items->fetch_assoc()){
        $featured_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Cozy Corner Cafe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Playfair Display', serif; }
        .smooth-scroll { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 smooth-scroll">

    <div x-data="{ mobileMenuOpen: false }">
        <!-- Using the shared header partial will keep the nav consistent -->
        <?php include 'partials/header.php'; ?>

        <!-- Main Content -->
        <main>
            <!-- Hero Section -->
            <section id="hero" class="pt-24 md:pt-32 pb-16 bg-white">
                <div class="container mx-auto px-6">
                    <div class="flex flex-col md:flex-row items-center gap-8">
                        <div class="md:w-1/2 text-center md:text-left">
                            <h1 class="text-4xl md:text-6xl font-bold font-display text-gray-900 leading-tight">
                                Where Every Sip & Bite <br class="hidden md:block"> Tells a Story.
                            </h1>
                            <p class="mt-4 text-lg text-gray-600">
                                Discover your new favorite spot for artisanal coffee, delectable pastries, and hearty meals. Fresh ingredients, cozy atmosphere.
                            </p>
                            <div class="mt-8 flex justify-center md:justify-start gap-4">
                                <a href="menu.php" class="px-8 py-3 font-semibold text-white bg-amber-600 rounded-full hover:bg-amber-700 transition duration-300 shadow-lg">View Full Menu</a>
                                <a href="#coupons" class="px-8 py-3 font-semibold text-amber-600 bg-white border border-amber-500 rounded-full hover:bg-amber-50 transition duration-300">See Today's Deals</a>
                            </div>
                        </div>
                        <div class="md:w-1/2 mt-8 md:mt-0">
                            <img src="https://placehold.co/600x400/FDBF60/333333?text=Cozy+Cafe" alt="A cozy scene from the cafe" class="rounded-xl shadow-2xl w-full h-auto">
                        </div>
                    </div>
                </div>
            </section>

            <!-- Featured Menu Section -->
            <section id="menu" class="py-16">
                <div class="container mx-auto px-6 text-center">
                    <h2 class="text-3xl font-bold font-display text-gray-900">Our Fan Favorites</h2>
                    <p class="mt-2 text-gray-600">A sneak peek at what our regulars love the most.</p>
                    
                    <?php if (!empty($featured_items)): ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 mt-12">
                            <?php foreach ($featured_items as $item): ?>
                                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:-translate-y-2 transition-transform duration-300">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full h-48 object-cover" onerror="this.onerror=null;this.src='https://placehold.co/400x300/FDBF60/333?text=Image+Not+Found';">
                                    <div class="p-6">
                                        <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars($item['description']); ?></p>
                                        <span class="font-bold text-amber-700">$<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="mt-12 text-gray-500">
                            <p>Our chefs are busy crafting our new featured items. Please check back soon!</p>
                        </div>
                    <?php endif; ?>

                    <div class="mt-12">
                         <a href="menu.php" class="px-8 py-3 font-semibold text-white bg-amber-600 rounded-full hover:bg-amber-700 transition duration-300 shadow-lg">Explore Full Menu</a>
                    </div>
                </div>
            </section>

            <!-- Coupon/Deals Section -->
            <section id="coupons" class="py-16">
                 <div class="container mx-auto px-6 text-center">
                    <h2 class="text-3xl font-bold font-display text-gray-900">Today's Hottest Deals</h2>
                    <p class="mt-2 text-gray-600 mb-12">Grab these before they're gone! Apply the code at checkout.</p>
                    
                    <?php if (!empty($active_coupons)): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            <?php foreach ($active_coupons as $coupon): ?>
                                <div class="bg-white border-2 border-dashed border-amber-400 rounded-lg shadow-lg p-6 flex flex-col items-center text-center transform hover:scale-105 transition-transform duration-300">
                                    <div class="bg-amber-500 text-white rounded-full h-20 w-20 flex items-center justify-center mb-4">
                                        <i class="fas fa-tag text-4xl"></i>
                                    </div>
                                    <h3 class="text-2xl font-bold text-amber-600">
                                        <?php echo $coupon['discount_type'] == 'percentage' ? htmlspecialchars($coupon['discount_value']) . '%' : '$' . htmlspecialchars(number_format($coupon['discount_value'], 2)); ?> OFF
                                    </h3>
                                    <p class="text-gray-600 mt-2">Use code at checkout</p>
                                    <p class="my-4 bg-gray-100 border border-gray-300 font-mono text-lg font-bold px-4 py-2 rounded-md"><?php echo htmlspecialchars($coupon['code']); ?></p>
                                    <p class="text-xs text-gray-500">
                                        <?php echo $coupon['expiry_date'] ? 'Expires on ' . date('M d, Y', strtotime($coupon['expiry_date'])) : 'Does not expire'; ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-amber-100 border border-amber-300 text-amber-800 p-8 rounded-lg text-center">
                             <p class="text-xl">No special deals today, but our menu is always delicious!</p>
                             <a href="menu.php" class="mt-4 inline-block font-semibold text-amber-700 hover:underline">Check out the menu</a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>

        <!-- Use the shared footer partial -->
        <?php include 'partials/footer.php'; ?>
    </div>
</body>
</html>
