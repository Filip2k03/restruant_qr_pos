<?php
$page_title = 'Tables & QR Codes';
require_once 'partials/header.php';

// Use QuickChart for QR code generation
function get_qr_code_url($table_id, $table_number = '') {
    $menu_url = BASE_URL . '/menu.php?table=' . $table_id;
    return 'https://quickchart.io/qr?text=' . urlencode($menu_url) . '&size=300';
}

// --- Handle POST requests for CRUD operations ---
$message = '';
$message_type = ''; // 'success' or 'error'
$show_form = false;
$edit_table = null;
$show_qr = false;
$qr_table_id = null;
$qr_table_number = null;

if (isset($_POST['add_table'])) {
    $table_number = trim($_POST['table_number']);
    if (!empty($table_number)) {
        $stmt_check = $conn->prepare("SELECT id FROM tables WHERE table_number = ?");
        $stmt_check->bind_param("s", $table_number);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $message = 'A table with this number already exists.';
            $message_type = 'error';
            $show_form = true;
        } else {
            $stmt = $conn->prepare("INSERT INTO tables (table_number, status) VALUES (?, 'free')");
            $stmt->bind_param("s", $table_number);
            if ($stmt->execute()) {
                $message = 'Table added successfully!';
                $message_type = 'success';
            } else {
                $message = 'Error: Could not add table.';
                $message_type = 'error';
                $show_form = true;
            }
            $stmt->close();
        }
        $stmt_check->close();
    } else {
        $message = 'Table number is required.';
        $message_type = 'error';
        $show_form = true;
    }
} elseif (isset($_POST['edit_table'])) {
    // Show edit form
    $edit_table_id = (int)$_POST['id'];
    $stmt = $conn->prepare("SELECT * FROM tables WHERE id = ?");
    $stmt->bind_param("i", $edit_table_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_table = $result->fetch_assoc();
    $show_form = true;
    $stmt->close();
} elseif (isset($_POST['update_table'])) {
    $id = (int)$_POST['id'];
    $table_number = trim($_POST['table_number']);
    $status = trim($_POST['status']);
    if (!empty($table_number)) {
        $stmt = $conn->prepare("UPDATE tables SET table_number = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssi", $table_number, $status, $id);
        if ($stmt->execute()) {
            $message = 'Table updated successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error: Could not update table.';
            $message_type = 'error';
            $show_form = true;
        }
        $stmt->close();
    } else {
        $message = 'Table number is required.';
        $message_type = 'error';
        $show_form = true;
    }
} elseif (isset($_POST['delete_table'])) {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("DELETE FROM tables WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = 'Table deleted successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error: Could not delete table. It might be linked to an order.';
        $message_type = 'error';
    }
    $stmt->close();
} elseif (isset($_POST['show_qr'])) {
    $show_qr = true;
    $qr_table_id = (int)$_POST['id'];
    $qr_table_number = $_POST['table_number'];
}

// Fetch all tables
$tables = $conn->query("SELECT * FROM tables ORDER BY table_number ASC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Manage Tables</h1>
    <form method="post" style="display:inline;">
        <button type="submit" name="show_add_form" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 font-semibold flex items-center gap-2">
            <i class="fas fa-plus"></i> Add Table
        </button>
    </form>
</div>

<!-- Display Message -->
<?php if ($message): ?>
<div class="mb-6 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<!-- Add/Edit Table Form -->
<?php
if ($show_form || isset($_POST['show_add_form'])):
    $is_edit = !!$edit_table;
    $form_title = $is_edit ? 'Edit Table' : 'Add New Table';
    $table_number = $is_edit ? htmlspecialchars($edit_table['table_number']) : '';
    $status = $is_edit ? $edit_table['status'] : 'free';
    $id = $is_edit ? (int)$edit_table['id'] : '';
?>
<div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mb-8">
    <h3 class="text-xl font-bold mb-4"><?php echo $form_title; ?></h3>
    <form method="POST" action="tables.php">
        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?php echo $id; ?>">
        <?php endif; ?>
        <div class="mb-4">
            <label for="table_number" class="block text-sm font-medium text-gray-700">Table Number / Name</label>
            <input type="text" name="table_number" id="table_number" value="<?php echo $table_number; ?>" required class="mt-1 block w-full input">
        </div>
        <?php if ($is_edit): ?>
        <div class="mb-4">
            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" id="status" class="mt-1 block w-full input">
                <option value="free" <?php if($status=='free') echo 'selected'; ?>>Free</option>
                <option value="in_use" <?php if($status=='in_use') echo 'selected'; ?>>In Use</option>
                <option value="needs_cleaning" <?php if($status=='needs_cleaning') echo 'selected'; ?>>Needs Cleaning</option>
            </select>
        </div>
        <?php endif; ?>
        <div class="flex justify-end space-x-3">
            <a href="tables.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400">Cancel</a>
            <?php if ($is_edit): ?>
                <button type="submit" name="update_table" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Update Table</button>
            <?php else: ?>
                <button type="submit" name="add_table" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Add Table</button>
            <?php endif; ?>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- QR Code Modal (inline) -->
<?php if ($show_qr && $qr_table_id): ?>
<div class="bg-white rounded-lg shadow-xl p-6 text-center mb-8">
    <h3 class="text-2xl font-bold mb-2">Table <?php echo htmlspecialchars($qr_table_number); ?></h3>
    <p class="mb-4 text-gray-600">Scan to view the menu and order.</p>
    <img src="<?php echo get_qr_code_url($qr_table_id, $qr_table_number); ?>" alt="QR Code" class="w-64 h-64 mx-auto border-4 border-gray-300 rounded-lg">
    <form method="get" action="tables.php">
        <button type="submit" class="mt-6 bg-gray-300 text-gray-800 px-6 py-2 rounded-md hover:bg-gray-400">Close</button>
    </form>
</div>
<?php endif; ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
        <?php foreach($tables as $table): ?>
        <div class="border rounded-lg p-4 text-center shadow-sm relative">
            <div class="absolute top-2 right-2">
                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php 
                    switch($table['status']) {
                        case 'free': echo 'bg-green-200 text-green-800'; break;
                        case 'in_use': echo 'bg-yellow-200 text-yellow-800'; break;
                        case 'needs_cleaning': echo 'bg-red-200 text-red-800'; break;
                    }
                ?>">
                    <?php echo str_replace('_', ' ', ucfirst($table['status'])); ?>
                </span>
            </div>
            <i class="fas fa-chair text-5xl text-gray-400 my-4"></i>
            <p class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($table['table_number']); ?></p>
            <div class="mt-4 flex justify-center items-center space-x-2">
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $table['id']; ?>">
                    <input type="hidden" name="table_number" value="<?php echo htmlspecialchars($table['table_number']); ?>">
                    <button type="submit" name="show_qr" class="text-gray-500 hover:text-green-600 p-2" title="Show QR Code">
                        <i class="fas fa-qrcode"></i>
                    </button>
                </form>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $table['id']; ?>">
                    <button type="submit" name="edit_table" class="text-gray-500 hover:text-blue-600 p-2" title="Edit Table">
                        <i class="fas fa-edit"></i>
                    </button>
                </form>
                <form method="POST" action="tables.php" onsubmit="return confirm('Are you sure?');" class="inline">
                    <input type="hidden" name="id" value="<?php echo $table['id']; ?>">
                    <button type="submit" name="delete_table" class="text-gray-500 hover:text-red-600 p-2" title="Delete Table">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<style>
.input { display: block; width: 100%; padding: 0.5rem 0.75rem; background-color: white; border: 1px solid #d1d5db; border-radius: 0.375rem; }
.input:focus { outline: 2px solid transparent; outline-offset: 2px; --tw-ring-color: #3b82f6; border-color: var(--tw-ring-color); }
</style>
<?php require_once 'partials/footer.php'; ?>
