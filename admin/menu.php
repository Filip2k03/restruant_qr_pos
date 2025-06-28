<?php
$page_title = 'Menu & Categories';
require_once 'partials/header.php';

// --- BREAD & BUTTER: Handle POST requests for all CRUD operations ---

$message = '';
$message_type = ''; // 'success' or 'error'

// -- CATEGORY ACTIONS --
if (isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        if ($stmt->execute()) {
            $message = 'Category added successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error: Could not add category.';
            $message_type = 'error';
        }
        $stmt->close();
    }
} elseif (isset($_POST['update_category'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    if (!empty($name)) {
        $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $description, $id);
        if ($stmt->execute()) {
            $message = 'Category updated successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error: Could not update category.';
            $message_type = 'error';
        }
        $stmt->close();
    }
} elseif (isset($_POST['delete_category'])) {
    $id = (int)$_POST['id'];
    // You might want to add a check here to prevent deleting categories with items.
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = 'Category deleted successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error: Could not delete category. It might be in use.';
        $message_type = 'error';
    }
    $stmt->close();
}

// -- MENU ITEM ACTIONS --
elseif (isset($_POST['add_menu_item'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $image_url = trim($_POST['image_url']) ?: null; // Use NULL if empty
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    $stmt = $conn->prepare("INSERT INTO menu_items (name, description, price, category_id, image_url, is_available) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdisi", $name, $description, $price, $category_id, $image_url, $is_available);
    if ($stmt->execute()) {
        $message = 'Menu item added successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error: Could not add menu item.';
        $message_type = 'error';
    }
    $stmt->close();

} elseif (isset($_POST['update_menu_item'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $image_url = trim($_POST['image_url']) ?: null;
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE menu_items SET name=?, description=?, price=?, category_id=?, image_url=?, is_available=? WHERE id = ?");
    $stmt->bind_param("ssdisii", $name, $description, $price, $category_id, $image_url, $is_available, $id);
    if ($stmt->execute()) {
        $message = 'Menu item updated successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error: Could not update menu item.';
        $message_type = 'error';
    }
    $stmt->close();
} elseif (isset($_POST['delete_menu_item'])) {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("DELETE FROM menu_items WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = 'Menu item deleted successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error: Could not delete menu item.';
        $message_type = 'error';
    }
    $stmt->close();
}


// --- DATA FETCHING: Get all data needed to display the page ---
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$menu_items = $conn->query("SELECT mi.*, c.name as category_name FROM menu_items mi JOIN categories c ON mi.category_id = c.id ORDER BY c.name, mi.name ASC")->fetch_all(MYSQLI_ASSOC);
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Manage Menu & Categories</h1>

<!-- Display Message -->
<?php if ($message): ?>
<div class="mb-6 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="{ 
    showCategoryModal: false, 
    showMenuModal: false, 
    isEditingCategory: false, 
    isEditingMenu: false,
    categoryData: {},
    menuData: {} 
}">

    <!-- Left Column: Categories -->
    <div class="lg:col-span-1 bg-white p-6 rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Categories</h2>
            <button @click="isEditingCategory = false; categoryData = {}; showCategoryModal = true" class="bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600 text-sm font-semibold">+ Add New</button>
        </div>
        <div class="space-y-3">
            <?php foreach($categories as $cat): ?>
            <div class="bg-gray-50 p-3 rounded-md flex justify-between items-center">
                <div>
                    <p class="font-semibold"><?php echo htmlspecialchars($cat['name']); ?></p>
                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($cat['description']); ?></p>
                </div>
                <div class="flex items-center space-x-2">
                    <button @click="isEditingCategory = true; categoryData = <?php echo htmlspecialchars(json_encode($cat)); ?>; showCategoryModal = true" class="text-gray-500 hover:text-blue-600"><i class="fas fa-edit"></i></button>
                    <form method="POST" action="menu.php" onsubmit="return confirm('Are you sure you want to delete this category? This might fail if it has menu items.');" class="inline">
                        <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                        <button type="submit" name="delete_category" class="text-gray-500 hover:text-red-600"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Right Column: Menu Items -->
    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Menu Items</h2>
            <button @click="isEditingMenu = false; menuData = {is_available: true}; showMenuModal = true" class="bg-green-500 text-white px-3 py-1 rounded-md hover:bg-green-600 text-sm font-semibold">+ Add New</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 border-b">
                        <th class="p-3 font-semibold">Item Name</th>
                        <th class="p-3 font-semibold">Category</th>
                        <th class="p-3 font-semibold">Price</th>
                        <th class="p-3 font-semibold">Status</th>
                        <th class="p-3 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($menu_items as $item): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3"><?php echo htmlspecialchars($item['name']); ?></td>
                        <td class="p-3 text-sm text-gray-600"><?php echo htmlspecialchars($item['category_name']); ?></td>
                        <td class="p-3">$<?php echo number_format($item['price'], 2); ?></td>
                        <td class="p-3">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $item['is_available'] ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'; ?>">
                                <?php echo $item['is_available'] ? 'Available' : 'Unavailable'; ?>
                            </span>
                        </td>
                        <td class="p-3 flex items-center space-x-2">
                            <button @click="isEditingMenu = true; menuData = <?php echo htmlspecialchars(json_encode($item)); ?>; showMenuModal = true" class="text-gray-500 hover:text-blue-600"><i class="fas fa-edit"></i></button>
                            <form method="POST" action="menu.php" onsubmit="return confirm('Are you sure you want to delete this menu item?');" class="inline">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <button type="submit" name="delete_menu_item" class="text-gray-500 hover:text-red-600"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Category Modal -->
    <div x-show="showCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40 p-4" @click.away="showCategoryModal = false" style="display: none;">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.stop>
            <h3 class="text-xl font-bold mb-4" x-text="isEditingCategory ? 'Edit Category' : 'Add New Category'"></h3>
            <form method="POST" action="menu.php">
                <input type="hidden" name="id" x-model="categoryData.id">
                <div class="mb-4">
                    <label for="cat_name" class="block text-sm font-medium text-gray-700">Category Name</label>
                    <input type="text" name="name" id="cat_name" x-model="categoryData.name" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="mb-4">
                    <label for="cat_desc" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="cat_desc" x-model="categoryData.description" rows="3" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" @click="showCategoryModal = false" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400">Cancel</button>
                    <button type="submit" x-show="isEditingCategory" name="update_category" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Update Category</button>
                    <button type="submit" x-show="!isEditingCategory" name="add_category" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Add Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Menu Item Modal -->
    <div x-show="showMenuModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40 p-4" @click.away="showMenuModal = false" style="display: none;">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg" @click.stop>
            <h3 class="text-xl font-bold mb-4" x-text="isEditingMenu ? 'Edit Menu Item' : 'Add New Menu Item'"></h3>
            <form method="POST" action="menu.php">
                <input type="hidden" name="id" x-model="menuData.id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Item Name</label>
                        <input type="text" name="name" x-model="menuData.name" required class="mt-1 block w-full input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="category_id" x-model="menuData.category_id" required class="mt-1 block w-full input">
                            <option value="">Select a category</option>
                            <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Price</label>
                        <input type="number" name="price" step="0.01" x-model="menuData.price" required class="mt-1 block w-full input">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Image URL (Optional)</label>
                        <input type="text" name="image_url" x-model="menuData.image_url" placeholder="https://example.com/image.jpg" class="mt-1 block w-full input">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" x-model="menuData.description" rows="3" class="mt-1 block w-full input"></textarea>
                    </div>
                    <div class="md:col-span-2 flex items-center">
                        <input type="checkbox" name="is_available" id="is_available" x-model="menuData.is_available" class="h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                        <label for="is_available" class="ml-2 block text-sm text-gray-900">Item is Available</label>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" @click="showMenuModal = false" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400">Cancel</button>
                    <button type="submit" x-show="isEditingMenu" name="update_menu_item" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">Update Item</button>
                    <button type="submit" x-show="!isEditingMenu" name="add_menu_item" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.input {
    display: block;
    width: 100%;
    padding: 0.5rem 0.75rem;
    background-color: white;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}
.input:focus {
    outline: 2px solid transparent;
    outline-offset: 2px;
    --tw-ring-color: #3b82f6;
    border-color: var(--tw-ring-color);
}
</style>

<?php require_once 'partials/footer.php'; ?>
