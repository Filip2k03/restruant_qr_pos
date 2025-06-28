<?php
$page_title = 'Dashboard';
require_once 'partials/header.php';

// Fetch stats for the dashboard
// Total Users
$total_users = $conn->query("SELECT COUNT(id) as count FROM users")->fetch_assoc()['count'];
// Total Menu Items
$total_items = $conn->query("SELECT COUNT(id) as count FROM menu_items")->fetch_assoc()['count'];
// Pending Orders
$pending_orders = $conn->query("SELECT COUNT(id) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
// Total Revenue (from completed orders)
$total_revenue = $conn->query("SELECT SUM(total_amount) as sum FROM orders WHERE status = 'completed'")->fetch_assoc()['sum'];

// Fetch recent orders
$recent_orders_result = $conn->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Dashboard</h1>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
        <div>
            <div class="text-sm text-gray-500">Total Users</div>
            <div class="text-3xl font-bold text-gray-800"><?php echo $total_users; ?></div>
        </div>
        <div class="bg-blue-100 text-blue-500 rounded-full h-12 w-12 flex items-center justify-center">
            <i class="fas fa-users text-xl"></i>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
        <div>
            <div class="text-sm text-gray-500">Menu Items</div>
            <div class="text-3xl font-bold text-gray-800"><?php echo $total_items; ?></div>
        </div>
        <div class="bg-green-100 text-green-500 rounded-full h-12 w-12 flex items-center justify-center">
            <i class="fas fa-utensils text-xl"></i>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
        <div>
            <div class="text-sm text-gray-500">Pending Orders</div>
            <div class="text-3xl font-bold text-gray-800"><?php echo $pending_orders; ?></div>
        </div>
        <div class="bg-yellow-100 text-yellow-500 rounded-full h-12 w-12 flex items-center justify-center">
            <i class="fas fa-hourglass-half text-xl"></i>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
        <div>
            <div class="text-sm text-gray-500">Total Revenue</div>
            <div class="text-3xl font-bold text-gray-800">$<?php echo number_format($total_revenue ?? 0, 2); ?></div>
        </div>
        <div class="bg-red-100 text-red-500 rounded-full h-12 w-12 flex items-center justify-center">
            <i class="fas fa-dollar-sign text-xl"></i>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Recent Orders</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="p-4 font-semibold">Order ID</th>
                    <th class="p-4 font-semibold">Customer</th>
                    <th class="p-4 font-semibold">Total</th>
                    <th class="p-4 font-semibold">Status</th>
                    <th class="p-4 font-semibold">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recent_orders_result->num_rows > 0): ?>
                    <?php while($order = $recent_orders_result->fetch_assoc()): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-4">#<?php echo $order['id']; ?></td>
                            <td class="p-4"><?php echo htmlspecialchars($order['user_name']); ?></td>
                            <td class="p-4">$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td class="p-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php 
                                    switch($order['status']) {
                                        case 'pending': echo 'bg-yellow-200 text-yellow-800'; break;
                                        case 'completed': echo 'bg-green-200 text-green-800'; break;
                                        case 'cancelled': echo 'bg-red-200 text-red-800'; break;
                                        default: echo 'bg-blue-200 text-blue-800';
                                    }
                                ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td class="p-4 text-sm text-gray-600"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center p-8 text-gray-500">No recent orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
