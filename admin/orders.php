<?php
$page_title = 'Manage Orders';
require_once 'partials/header.php';

// Handle order status update
if (isset($_POST['update_order_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    $stmt->close();
    // A simple way to refresh without a formal message system
    header("Location: orders.php?status_updated=true");
    exit();
}

// Fetch all orders with user and table info
$orders_result = $conn->query("
    SELECT o.*, u.name as user_name, t.table_number 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    LEFT JOIN tables t ON o.table_id = t.id 
    ORDER BY o.created_at DESC
");
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Manage Orders</h1>

<?php if (isset($_GET['status_updated'])): ?>
<div class="mb-6 p-4 rounded-md bg-green-100 text-green-800">
    Order status updated successfully!
</div>
<?php endif; ?>

<div class="bg-white p-6 rounded-lg shadow-md" x-data="{ openOrderId: null }">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="p-4 font-semibold">Details</th>
                    <th class="p-4 font-semibold">Customer</th>
                    <th class="p-4 font-semibold">Type / Table</th>
                    <th class="p-4 font-semibold">Total</th>
                    <th class="p-4 font-semibold">Payment</th>
                    <th class="p-4 font-semibold">Date</th>
                    <th class="p-4 font-semibold">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders_result->num_rows > 0): ?>
                    <?php while($order = $orders_result->fetch_assoc()): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-4">
                                <button @click="openOrderId = (openOrderId === <?php echo $order['id']; ?> ? null : <?php echo $order['id']; ?>)" class="text-blue-500 hover:underline">
                                    #<?php echo $order['id']; ?> <i class="fas" :class="{'fa-chevron-down': openOrderId !== <?php echo $order['id']; ?>, 'fa-chevron-up': openOrderId === <?php echo $order['id']; ?>}"></i>
                                </button>
                            </td>
                            <td class="p-4"><?php echo htmlspecialchars($order['user_name']); ?></td>
                            <td class="p-4">
                                <?php echo ucfirst(str_replace('_', ' ', $order['order_type'])); ?>
                                <?php if($order['table_number']): ?>
                                    <span class="text-gray-500">(<?php echo htmlspecialchars($order['table_number']); ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 font-bold">$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td class="p-4">
                                <span class="text-xs font-semibold"><?php echo strtoupper($order['payment_method']); ?></span><br>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $order['payment_status'] === 'paid' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </td>
                            <td class="p-4 text-sm text-gray-600"><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                            <td class="p-4">
                                <form method="POST" action="orders.php" class="flex items-center gap-2">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status" class="text-sm border-gray-300 rounded-md">
                                        <option value="pending" <?php if($order['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                        <option value="preparing" <?php if($order['status'] == 'preparing') echo 'selected'; ?>>Preparing</option>
                                        <option value="ready" <?php if($order['status'] == 'ready') echo 'selected'; ?>>Ready</option>
                                        <option value="completed" <?php if($order['status'] == 'completed') echo 'selected'; ?>>Completed</option>
                                        <option value="cancelled" <?php if($order['status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_order_status" class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600" title="Update Status"><i class="fas fa-check"></i></button>
                                </form>
                            </td>
                        </tr>
                        <tr x-show="openOrderId === <?php echo $order['id']; ?>" style="display: none;">
                            <td colspan="7" class="p-4 bg-gray-100">
                                <h4 class="font-bold mb-2">Order Items:</h4>
                                <?php
                                $order_items_stmt = $conn->prepare("SELECT oi.quantity, oi.price, mi.name FROM order_items oi JOIN menu_items mi ON oi.menu_item_id = mi.id WHERE oi.order_id = ?");
                                $order_items_stmt->bind_param("i", $order['id']);
                                $order_items_stmt->execute();
                                $order_items_result = $order_items_stmt->get_result();
                                ?>
                                <ul class="list-disc pl-5 text-sm">
                                <?php while($item = $order_items_result->fetch_assoc()): ?>
                                    <li><?php echo $item['quantity']; ?> x <?php echo htmlspecialchars($item['name']); ?> @ $<?php echo number_format($item['price'], 2); ?></li>
                                <?php endwhile; ?>
                                </ul>
                                <?php $order_items_stmt->close(); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center p-8 text-gray-500">No orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
