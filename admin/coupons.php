<?php
$page_title = 'Manage Coupons';
require_once 'partials/header.php';

// --- Handle POST requests for CRUD operations ---
$message = '';
$message_type = ''; // 'success' or 'error'

if (isset($_POST['add_coupon'])) {
    $code = strtoupper(trim($_POST['code']));
    $discount_type = $_POST['discount_type'];
    $discount_value = (float)$_POST['discount_value'];
    $usage_limit = (int)$_POST['usage_limit'];
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $stmt = $conn->prepare("INSERT INTO coupons (code, discount_type, discount_value, usage_limit, expiry_date, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdisi", $code, $discount_type, $discount_value, $usage_limit, $expiry_date, $is_active);
    if ($stmt->execute()) {
        $message = 'Coupon added successfully!'; $message_type = 'success';
    } else {
        $message = 'Error: Could not add coupon. The code may already exist.'; $message_type = 'error';
    }
    $stmt->close();

} elseif (isset($_POST['update_coupon'])) {
    $id = (int)$_POST['id'];
    $code = strtoupper(trim($_POST['code']));
    $discount_type = $_POST['discount_type'];
    $discount_value = (float)$_POST['discount_value'];
    $usage_limit = (int)$_POST['usage_limit'];
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $stmt = $conn->prepare("UPDATE coupons SET code=?, discount_type=?, discount_value=?, usage_limit=?, expiry_date=?, is_active=? WHERE id=?");
    $stmt->bind_param("ssdisii", $code, $discount_type, $discount_value, $usage_limit, $expiry_date, $is_active, $id);
    if ($stmt->execute()) {
        $message = 'Coupon updated successfully!'; $message_type = 'success';
    } else {
        $message = 'Error: Could not update coupon.'; $message_type = 'error';
    }
    $stmt->close();
} elseif (isset($_POST['delete_coupon'])) {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("DELETE FROM coupons WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = 'Coupon deleted successfully!'; $message_type = 'success';
    } else {
        $message = 'Error: Could not delete coupon.'; $message_type = 'error';
    }
    $stmt->close();
}


// Fetch all coupons
$coupons = $conn->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Manage Coupons</h1>
    <button @click="isEditing = false; couponData = { is_active: true }; showModal = true" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 font-semibold flex items-center gap-2">
        <i class="fas fa-plus"></i> Add Coupon
    </button>
</div>

<!-- Display Message -->
<?php if ($message): ?>
<div class="mb-6 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<div class="bg-white p-6 rounded-lg shadow-md" x-data="{ showModal: false, isEditing: false, couponData: {} }">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="p-3 font-semibold">Code</th>
                    <th class="p-3 font-semibold">Discount</th>
                    <th class="p-3 font-semibold">Usage Limit</th>
                    <th class="p-3 font-semibold">Expires</th>
                    <th class="p-3 font-semibold">Status</th>
                    <th class="p-3 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($coupons as $coupon): ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 font-mono font-bold"><?php echo htmlspecialchars($coupon['code']); ?></td>
                    <td class="p-3"><?php echo $coupon['discount_type'] == 'percentage' ? $coupon['discount_value'] . '%' : '$' . number_format($coupon['discount_value'], 2); ?></td>
                    <td class="p-3"><?php echo $coupon['usage_limit']; ?></td>
                    <td class="p-3"><?php echo $coupon['expiry_date'] ? date('M d, Y', strtotime($coupon['expiry_date'])) : 'Never'; ?></td>
                    <td class="p-3">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $coupon['is_active'] ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'; ?>">
                            <?php echo $coupon['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td class="p-3 flex items-center space-x-2">
                        <button @click="isEditing = true; couponData = <?php echo htmlspecialchars(json_encode($coupon)); ?>; showModal = true" class="text-gray-500 hover:text-blue-600"><i class="fas fa-edit"></i></button>
                        <form method="POST" action="coupons.php" onsubmit="return confirm('Are you sure?');" class="inline">
                            <input type="hidden" name="id" value="<?php echo $coupon['id']; ?>">
                            <button type="submit" name="delete_coupon" class="text-gray-500 hover:text-red-600"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Add/Edit Coupon Modal -->
    <div x-show="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40 p-4" @click.away="showModal = false" style="display: none;">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg" @click.stop>
            <h3 class="text-xl font-bold mb-4" x-text="isEditing ? 'Edit Coupon' : 'Add New Coupon'"></h3>
            <form method="POST" action="coupons.php">
                <input type="hidden" name="id" x-model="couponData.id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Coupon Code</label>
                        <input type="text" name="code" x-model="couponData.code" required class="mt-1 block w-full input uppercase">
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-700">Usage Limit</label>
                        <input type="number" name="usage_limit" min="0" x-model="couponData.usage_limit" required class="mt-1 block w-full input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Discount Type</label>
                        <select name="discount_type" x-model="couponData.discount_type" required class="mt-1 block w-full input">
                            <option value="percentage">Percentage</option>
                            <option value="fixed">Fixed Amount</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Discount Value</label>
                        <input type="number" name="discount_value" step="0.01" min="0" x-model="couponData.discount_value" required class="mt-1 block w-full input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Expiry Date (Optional)</label>
                        <input type="date" name="expiry_date" x-model="couponData.expiry_date" class="mt-1 block w-full input">
                    </div>
                    <div class="flex items-center pt-6">
                        <input type="checkbox" name="is_active" id="is_active" x-model="couponData.is_active" class="h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">Coupon is Active</label>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" @click="showModal = false" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400">Cancel</button>
                    <button type="submit" x-show="isEditing" name="update_coupon" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Update Coupon</button>
                    <button type="submit" x-show="!isEditing" name="add_coupon" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Add Coupon</button>
                </div>
            </form>
        </div>
    </div>
</div>
<style>
.input { display: block; width: 100%; padding: 0.5rem 0.75rem; background-color: white; border: 1px solid #d1d5db; border-radius: 0.375rem; }
.input:focus { outline: 2px solid transparent; outline-offset: 2px; --tw-ring-color: #3b82f6; border-color: var(--tw-ring-color); }
</style>
<?php require_once 'partials/footer.php'; ?>
