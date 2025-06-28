<?php
$page_title = 'Checkout';
require_once 'partials/header.php';
require_login();

$user_id = $_SESSION['user_id'];
$errors = [];
$message = '';
$message_type = '';

// Fetch cart items to make sure cart is not empty
$cart_items_query = $conn->prepare("SELECT c.quantity, mi.price FROM cart c JOIN menu_items mi ON c.menu_item_id = mi.id WHERE c.user_id = ?");
$cart_items_query->bind_param("i", $user_id);
$cart_items_query->execute();
$cart_result = $cart_items_query->get_result();
if ($cart_result->num_rows === 0) {
    header('Location: menu.php'); // Redirect to menu if cart is empty
    exit();
}
$cart_items_query->close();

// Fetch available tables for dine-in
$available_tables = $conn->query("SELECT id, table_number FROM tables WHERE status = 'free' ORDER BY table_number ASC")->fetch_all(MYSQLI_ASSOC);

// --- HANDLE ORDER SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_type = $_POST['order_type'];
    $payment_method = $_POST['payment_method'];
    $table_id = ($order_type === 'dine_in' && isset($_POST['table_id'])) ? (int)$_POST['table_id'] : null;
    $coupon_code = strtoupper(trim($_POST['coupon_code']));

    // --- Validation ---
    if ($order_type === 'dine_in' && empty($table_id)) $errors[] = "Please select a table for dine-in.";
    if ($order_type === 'take_away' && $payment_method === 'cash') $errors[] = "Cash payment is not available for take away.";
    
    if (empty($errors)) {
        // --- Transaction Start ---
        $conn->begin_transaction();

        try {
            // 1. Fetch cart items and calculate subtotal
            $cart_sql = "SELECT c.quantity, mi.id as menu_item_id, mi.name, mi.price FROM cart c JOIN menu_items mi ON c.menu_item_id = mi.id WHERE c.user_id = ?";
            $cart_stmt = $conn->prepare($cart_sql);
            $cart_stmt->bind_param("i", $user_id);
            $cart_stmt->execute();
            $items_in_cart = $cart_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $cart_stmt->close();
            
            $subtotal = 0;
            foreach ($items_in_cart as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }

            // 2. Validate and apply coupon
            $discount_amount = 0;
            if (!empty($coupon_code)) {
                $today = date('Y-m-d');
                $coupon_stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = TRUE AND usage_limit > 0 AND (expiry_date IS NULL OR expiry_date >= ?)");
                $coupon_stmt->bind_param("ss", $coupon_code, $today);
                $coupon_stmt->execute();
                $coupon_result = $coupon_stmt->get_result();
                if ($coupon = $coupon_result->fetch_assoc()) {
                    if ($coupon['discount_type'] === 'percentage') {
                        $discount_amount = $subtotal * ($coupon['discount_value'] / 100);
                    } else {
                        $discount_amount = $coupon['discount_value'];
                    }
                    // Decrease usage limit
                    $conn->query("UPDATE coupons SET usage_limit = usage_limit - 1 WHERE id = " . $coupon['id']);
                } else {
                    throw new Exception("Invalid or expired coupon code.");
                }
                $coupon_stmt->close();
            }

            $total_amount = $subtotal - $discount_amount;
            if ($total_amount < 0) $total_amount = 0; // Prevent negative total

            // 3. Create the order
            $order_stmt = $conn->prepare("INSERT INTO orders (user_id, table_id, order_type, total_amount, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, 'unpaid')");
            $order_stmt->bind_param("iisds", $user_id, $table_id, $order_type, $total_amount, $payment_method);
            $order_stmt->execute();
            $order_id = $conn->insert_id;
            if (!$order_id) throw new Exception("Could not create order.");

            // 4. Insert order items
            $order_items_stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach($items_in_cart as $item) {
                $order_items_stmt->bind_param("iiid", $order_id, $item['menu_item_id'], $item['quantity'], $item['price']);
                $order_items_stmt->execute();
            }
            $order_items_stmt->close();

            // 5. Update table status if dine-in
            if ($order_type === 'dine_in' && $table_id) {
                $conn->query("UPDATE tables SET status = 'in_use' WHERE id = $table_id");
            }
            
            // 6. Clear user's cart
            $clear_cart_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $clear_cart_stmt->bind_param("i", $user_id);
            $clear_cart_stmt->execute();
            $clear_cart_stmt->close();
            
            // --- If all good, commit transaction ---
            $conn->commit();
            header("Location: my_orders.php?order_success=true");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = $e->getMessage();
        }
    }
}


// --- DATA FETCHING FOR DISPLAY ---
$cart_items_display = [];
$subtotal_display = 0;

$sql_display = "SELECT c.id as cart_id, c.quantity, mi.id as menu_item_id, mi.name, mi.price, mi.image_url
        FROM cart c
        JOIN menu_items mi ON c.menu_item_id = mi.id
        WHERE c.user_id = ?";
$stmt_display = $conn->prepare($sql_display);
$stmt_display->bind_param("i", $user_id);
$stmt_display->execute();
$result_display = $stmt_display->get_result();

while ($row = $result_display->fetch_assoc()) {
    $cart_items_display[] = $row;
    $subtotal_display += $row['price'] * $row['quantity'];
}
$stmt_display->close();

?>
<div class="text-center mb-12">
    <h1 class="text-4xl font-bold font-display text-gray-900">Checkout</h1>
    <p class="mt-2 text-gray-600">You're just a few steps away from your delicious meal.</p>
</div>

<!-- Display Errors -->
<?php if (!empty($errors)): ?>
    <div class="max-w-4xl mx-auto mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Please fix the following issues:</strong>
        <ul class="mt-2 list-disc list-inside">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="checkout.php" method="POST" class="grid grid-cols-1 lg:grid-cols-5 gap-8 max-w-6xl mx-auto" x-data="{ orderType: 'take_away' }">
    <!-- Left Side: Order Options -->
    <div class="lg:col-span-3 bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-6">1. Your Details</h2>
        
        <!-- Order Type -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Order Type</label>
            <div class="flex gap-4">
                <label class="flex-1 border p-4 rounded-lg flex items-center gap-4 cursor-pointer" :class="{ 'bg-amber-50 border-amber-500 ring-2 ring-amber-500': orderType === 'take_away' }">
                    <input type="radio" name="order_type" value="take_away" x-model="orderType" class="hidden">
                    <i class="fas fa-shopping-bag text-2xl text-amber-600"></i>
                    <div>
                        <p class="font-semibold">Take Away</p>
                        <p class="text-xs text-gray-500">Pick up your order from the counter.</p>
                    </div>
                </label>
                <label class="flex-1 border p-4 rounded-lg flex items-center gap-4 cursor-pointer" :class="{ 'bg-amber-50 border-amber-500 ring-2 ring-amber-500': orderType === 'dine_in' }">
                    <input type="radio" name="order_type" value="dine_in" x-model="orderType" class="hidden">
                    <i class="fas fa-chair text-2xl text-amber-600"></i>
                     <div>
                        <p class="font-semibold">Dine-In</p>
                        <p class="text-xs text-gray-500">Enjoy your meal at our cafe.</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Table Selection (for Dine-In) -->
        <div x-show="orderType === 'dine_in'" x-transition class="mb-6">
            <label for="table_id" class="block text-sm font-medium text-gray-700">Select Your Table</label>
            <select name="table_id" id="table_id" class="mt-1 block w-full input">
                <option value="">-- Please select a table --</option>
                <?php foreach($available_tables as $table): ?>
                    <option value="<?php echo $table['id']; ?>"><?php echo htmlspecialchars($table['table_number']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Payment Method -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold mb-4 mt-8">2. Payment Method</h2>
            <select name="payment_method" class="mt-1 block w-full input" required>
                <template x-if="orderType !== 'take_away'">
                    <option value="cash">Cash (Pay at Counter)</option>
                </template>
                <option value="kbzpay">KBZPay</option>
                <option value="wavepay">WavePay</option>
                <option value="ayapay">AYAPay</option>
                 <template x-if="orderType === 'take_away'">
                    <option value="cod">Cash on Delivery (COD)</option>
                </template>
            </select>
            <p class="text-xs text-gray-500 mt-2">For digital payments, please show the transaction receipt at the counter.</p>
        </div>

    </div>

    <!-- Right Side: Order Summary -->
    <div class="lg:col-span-2">
        <div class="bg-white p-6 rounded-lg shadow-md sticky top-24">
            <h2 class="text-2xl font-bold mb-4">Order Summary</h2>
            <div class="space-y-4 max-h-64 overflow-y-auto pr-2">
                <?php foreach($cart_items_display as $item): ?>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" class="w-12 h-12 rounded-md object-cover">
                        <div>
                            <p class="font-semibold text-sm"><?php echo htmlspecialchars($item['name']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo $item['quantity']; ?> x $<?php echo number_format($item['price'], 2); ?></p>
                        </div>
                    </div>
                    <p class="font-semibold text-sm">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="border-t pt-4 mt-4">
                 <div class="mb-4">
                    <label for="coupon_code" class="block text-sm font-medium text-gray-700">Have a voucher?</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <input type="text" name="coupon_code" id="coupon_code" placeholder="ENTER CODE" class="flex-1 block w-full rounded-none rounded-l-md input uppercase">
                        <!-- A button to apply via AJAX would be better, but for PHP-only a note is fine -->
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Discount will be applied on the final order.</p>
                </div>

                <div class="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span>$<?php echo number_format($subtotal_display, 2); ?></span>
                </div>
                 <div class="flex justify-between text-gray-600 mt-1">
                    <span>Discount</span>
                    <span class="text-green-600">- $...</span>
                </div>
                <div class="flex justify-between font-bold text-xl mt-4">
                    <span>Total (Approx.)</span>
                    <span>$<?php echo number_format($subtotal_display, 2); ?></span>
                </div>
            </div>

            <button type="submit" class="mt-6 w-full bg-amber-600 text-white py-3 rounded-full hover:bg-amber-700 transition font-semibold text-lg">
                Place Order
            </button>
        </div>
    </div>
</form>

<style>
.input { display: block; width: 100%; padding: 0.75rem; background-color: white; border: 1px solid #d1d5db; border-radius: 0.375rem; }
.input:focus { outline: 2px solid transparent; outline-offset: 2px; --tw-ring-color: #f59e0b; border-color: var(--tw-ring-color); }
</style>

<?php require_once 'partials/footer.php'; ?>
