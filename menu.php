<?php
$page_title = 'Our Menu';
require_once 'partials/header.php';

// This page requires a user to be logged in.
require_login();

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = ''; // 'success' or 'error'

// Handle Add to Cart action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $menu_item_id = (int)$_POST['menu_item_id'];
    $quantity = 1; // Add one item at a time

    // Check if the item already exists in the cart for this user
    $stmt_check = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND menu_item_id = ?");
    $stmt_check->bind_param("ii", $user_id, $menu_item_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Item exists, update the quantity
        $cart_item = $result_check->fetch_assoc();
        $new_quantity = $cart_item['quantity'] + $quantity;
        $stmt_update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt_update->bind_param("ii", $new_quantity, $cart_item['id']);
        $stmt_update->execute();
        $stmt_update->close();
        $message = "Item quantity updated in your cart!";
    } else {
        // Item does not exist, insert new cart item
        $stmt_insert = $conn->prepare("INSERT INTO cart (user_id, menu_item_id, quantity) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("iii", $user_id, $menu_item_id, $quantity);
        $stmt_insert->execute();
        $stmt_insert->close();
        $message = "Item added to your cart!";
    }
    
    $stmt_check->close();
    $message_type = 'success';
    
    // Refresh the header to update cart count
    // A bit of a hack, but effective without a full page reload or JS framework
    echo "<meta http-equiv='refresh' content='2;url=menu.php'>";
}

// Fetch categories and menu items
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$menu_items_by_cat = [];

foreach ($categories as $category) {
    $stmt = $conn->prepare("SELECT * FROM menu_items WHERE category_id = ? AND is_available = TRUE ORDER BY name ASC");
    $stmt->bind_param("i", $category['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $menu_items_by_cat[$category['name']] = $result->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
}
?>

<div class="text-center mb-12">
    <h1 class="text-4xl font-bold font-display text-gray-900">Our Menu</h1>
    <p class="mt-2 text-gray-600">Explore our wide selection of delicious offerings.</p>
</div>

<!-- Display Success/Error Message -->
<?php if ($message): ?>
    <div class="mb-6 max-w-2xl mx-auto text-center p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php if (empty($menu_items_by_cat)): ?>
    <p class="text-center text-gray-500">Our menu is currently empty. Please check back later!</p>
<?php else: ?>
    <?php foreach ($menu_items_by_cat as $category_name => $menu_items): ?>
        <section id="category-<?php echo strtolower(str_replace(' ', '-', $category_name)); ?>" class="mb-12">
            <h2 class="text-3xl font-bold font-display text-amber-800 border-b-2 border-amber-200 pb-2 mb-8"><?php echo htmlspecialchars($category_name); ?></h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($menu_items as $item): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full h-56 object-cover" onerror="this.onerror=null;this.src='https://placehold.co/400x300/FDBF60/333?text=Image+Not+Found';">
                        <div class="p-6 flex-grow flex flex-col">
                            <h3 class="text-xl font-semibold mb-2 text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="text-gray-600 text-sm mb-4 flex-grow"><?php echo htmlspecialchars($item['description']); ?></p>
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-xl text-amber-700">$<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></span>
                                <form action="menu.php" method="POST">
                                    <input type="hidden" name="menu_item_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="add_to_cart" class="px-4 py-2 text-sm font-semibold text-white bg-amber-600 rounded-full hover:bg-amber-700 transition duration-300 flex items-center gap-2">
                                        <i class="fa-solid fa-cart-plus"></i> Add
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once 'partials/footer.php'; ?>
