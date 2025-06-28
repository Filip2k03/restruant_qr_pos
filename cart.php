<?php
$page_title = 'My Cart';
require_once 'partials/header.php';

// This page requires a user to be logged in.
require_login();

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = ''; // 'success' or 'error'

// Handle cart updates (remove or change quantity)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle item removal
    if (isset($_POST['remove_item'])) {
        $cart_id = (int)$_POST['cart_id'];
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_id, $user_id);
        if ($stmt->execute()) {
            $message = "Item removed from cart.";
            $message_type = 'success';
        }
        $stmt->close();
    }

    // Handle quantity update
    if (isset($_POST['update_quantity'])) {
        $cart_id = (int)$_POST['cart_id'];
        $quantity = (int)$_POST['quantity'];

        if ($quantity > 0) {
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
            $stmt->execute();
            $stmt->close();
            $message = "Cart updated.";
            $message_type = 'success';
        } else {
            // If quantity is 0 or less, remove the item
            $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $cart_id, $user_id);
            $stmt->execute();
            $stmt->close();
            $message = "Item removed from cart.";
            $message_type = 'success';
        }
    }
     echo "<meta http-equiv='refresh' content='1;url=cart.php'>";
}


// Fetch cart items for the user
$cart_items = [];
$subtotal = 0;

$sql = "SELECT c.id as cart_id, c.quantity, mi.id as menu_item_id, mi.name, mi.price, mi.image_url
        FROM cart c
        JOIN menu_items mi ON c.menu_item_id = mi.id
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
        $subtotal += $row['price'] * $row['quantity'];
    }
}
$stmt->close();
?>

<div class="text-center mb-12">
    <h1 class="text-4xl font-bold font-display text-gray-900">Your Shopping Cart</h1>
</div>

<!-- Display Success/Error Message -->
<?php if ($message): ?>
    <div class="mb-6 max-w-2xl mx-auto text-center p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php if (empty($cart_items)): ?>
    <div class="text-center py-16">
        <i class="fa-solid fa-cart-arrow-down text-6xl text-gray-300"></i>
        <p class="text-center text-gray-500 mt-4 text-xl">Your cart is empty.</p>
        <a href="menu.php" class="mt-6 inline-block px-8 py-3 font-semibold text-white bg-amber-600 rounded-full hover:bg-amber-700 transition duration-300 shadow-lg">
            Start Ordering
        </a>
    </div>
<?php else: ?>
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="hidden md:grid grid-cols-6 gap-4 font-bold p-4 border-b">
            <div class="col-span-2">Product</div>
            <div>Price</div>
            <div>Quantity</div>
            <div>Total</div>
            <div>Action</div>
        </div>

        <?php foreach ($cart_items as $item): ?>
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4 items-center p-4 border-b">
                <!-- Product -->
                <div class="col-span-2 flex items-center gap-4">
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-20 h-20 rounded-md object-cover" onerror="this.onerror=null;this.src='https://placehold.co/100x100/FDBF60/333?text=Image';">
                    <div>
                        <p class="font-semibold"><?php echo htmlspecialchars($item['name']); ?></p>
                        <span class="md:hidden text-sm text-gray-600">$<?php echo number_format($item['price'], 2); ?></span>
                    </div>
                </div>

                <!-- Price (hidden on mobile) -->
                <div class="hidden md:block">
                    $<?php echo number_format($item['price'], 2); ?>
                </div>

                <!-- Quantity -->
                <div>
                    <form action="cart.php" method="POST" class="flex items-center">
                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="99" class="w-16 text-center border-gray-300 rounded-md">
                        <button type="submit" name="update_quantity" class="ml-2 text-gray-500 hover:text-green-600" title="Update Quantity">
                             <i class="fa-solid fa-check"></i>
                        </button>
                    </form>
                </div>

                <!-- Total -->
                <div class="font-bold">
                    <span class="md:hidden">Total: </span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                </div>

                <!-- Action -->
                <div>
                     <form action="cart.php" method="POST">
                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                        <button type="submit" name="remove_item" class="text-red-500 hover:text-red-700" title="Remove Item">
                            <i class="fa-solid fa-trash-can text-lg"></i>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Cart Summary -->
        <div class="p-6 bg-gray-50 flex justify-end">
            <div class="w-full md:w-1/3">
                <h3 class="text-xl font-semibold mb-4">Cart Summary</h3>
                <div class="flex justify-between text-lg">
                    <span>Subtotal</span>
                    <span class="font-bold">$<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <p class="text-sm text-gray-500 mt-2">Taxes and shipping calculated at checkout.</p>
                <a href="checkout.php" class="mt-6 w-full text-center block px-8 py-3 font-semibold text-white bg-amber-600 rounded-full hover:bg-amber-700 transition duration-300 shadow-lg">
                    Proceed to Checkout
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'partials/footer.php'; ?>
