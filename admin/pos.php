<?php
session_start();
$page_title = 'Mini POS';
require_once 'partials/header.php';

// Fetch available menu items
$menu_items = $conn->query("
    SELECT mi.*, c.name as category_name 
    FROM menu_items mi
    JOIN categories c ON mi.category_id = c.id
    WHERE mi.is_available = TRUE 
    ORDER BY c.name, mi.name ASC
")->fetch_all(MYSQLI_ASSOC);

// Group items by category
$items_by_category = [];
foreach ($menu_items as $item) {
    $items_by_category[$item['category_name']][] = $item;
}

// Initialize cart in session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart'])) {
        $item_id = (int)$_POST['item_id'];
        $item = null;
        foreach ($menu_items as $mi) {
            if ($mi['id'] == $item_id) {
                $item = $mi;
                break;
            }
        }
        if ($item) {
            if (isset($_SESSION['cart'][$item_id])) {
                $_SESSION['cart'][$item_id]['quantity']++;
            } else {
                $_SESSION['cart'][$item_id] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => 1
                ];
            }
        }
    }
    if (isset($_POST['update_quantity'])) {
        $item_id = (int)$_POST['item_id'];
        $quantity = (int)$_POST['quantity'];
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$item_id]);
        } else {
            if (isset($_SESSION['cart'][$item_id])) {
                $_SESSION['cart'][$item_id]['quantity'] = $quantity;
            }
        }
    }
    if (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
    }
    if (isset($_POST['complete_bill'])) {
        // Here you would handle saving the bill to the database, etc.
        $_SESSION['cart'] = [];
        $bill_completed = true;
    }
}

// Calculate totals
$cart = $_SESSION['cart'];
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$tax = $subtotal * 0.10;
$total = $subtotal + $tax;
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left: Menu Items -->
    <div class="lg:col-span-2">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Point of Sale</h1>
        <div class="bg-white p-4 rounded-lg shadow-md">
            <?php foreach ($items_by_category as $category => $items): ?>
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-amber-800 border-b pb-2 mb-4"><?php echo htmlspecialchars($category); ?></h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <?php foreach($items as $item): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" name="add_to_cart" class="block text-left p-3 border rounded-lg hover:bg-amber-50 hover:shadow-md transition w-full">
                                    <p class="font-semibold text-sm"><?php echo htmlspecialchars($item['name']); ?></p>
                                    <p class="text-xs text-gray-600">$<?php echo number_format($item['price'], 2); ?></p>
                                </button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Right: Cart/Bill -->
    <div class="lg:col-span-1">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Current Bill</h1>
        <div class="bg-white p-4 rounded-lg shadow-md sticky top-24">
            <div class="min-h-[200px] max-h-[40vh] overflow-y-auto pr-2">
                <?php if (empty($cart)): ?>
                    <p class="text-gray-500 text-center py-16">Click an item to add it to the bill.</p>
                <?php else: ?>
                    <?php foreach ($cart as $item): ?>
                        <div class="flex justify-between items-center mb-3">
                            <div>
                                <p class="font-semibold text-sm"><?php echo htmlspecialchars($item['name']); ?></p>
                                <p class="text-xs text-gray-500">$<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="quantity" value="<?php echo $item['quantity'] - 1; ?>">
                                    <button type="submit" name="update_quantity" class="w-6 h-6 bg-gray-200 rounded">-</button>
                                </form>
                                <span class="w-6 text-center"><?php echo $item['quantity']; ?></span>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="quantity" value="<?php echo $item['quantity'] + 1; ?>">
                                    <button type="submit" name="update_quantity" class="w-6 h-6 bg-gray-200 rounded">+</button>
                                </form>
                                <span class="font-bold text-sm w-16 text-right">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="border-t pt-4 mt-4">
                <div class="flex justify-between font-semibold">
                    <span>Subtotal</span>
                    <span>$<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="flex justify-between font-semibold mt-2">
                    <span>Tax (10%)</span>
                    <span>$<?php echo number_format($tax, 2); ?></span>
                </div>
                <div class="flex justify-between font-bold text-xl mt-2 text-amber-700">
                    <span>Total</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>
            </div>

            <div class="mt-6">
                <form method="post" style="display:inline;">
                    <button type="submit" name="clear_cart" class="w-full bg-red-500 text-white py-2 rounded-md hover:bg-red-600 transition mb-2">Clear Bill</button>
                </form>
                <form method="post" style="display:inline;">
                    <button type="submit" name="complete_bill" class="w-full bg-green-500 text-white py-3 rounded-md hover:bg-green-600 transition" <?php if (empty($cart)) echo 'disabled style="background-color: #ccc;"'; ?>>Mark as Paid & Complete</button>
                </form>
                <?php if (!empty($bill_completed)): ?>
                    <div class="mt-2 text-green-700 font-bold text-center">Bill completed!</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
