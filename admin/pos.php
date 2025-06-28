<?php
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
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="posSystem()">
    <!-- Left: Menu Items -->
    <div class="lg:col-span-2">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Point of Sale</h1>
        <div class="bg-white p-4 rounded-lg shadow-md">
            <?php foreach ($items_by_category as $category => $items): ?>
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-amber-800 border-b pb-2 mb-4"><?php echo htmlspecialchars($category); ?></h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <?php foreach($items as $item): ?>
                            <button @click="addToCart(<?php echo htmlspecialchars(json_encode($item)); ?>)" class="block text-left p-3 border rounded-lg hover:bg-amber-50 hover:shadow-md transition">
                                <p class="font-semibold text-sm"><?php echo htmlspecialchars($item['name']); ?></p>
                                <p class="text-xs text-gray-600">$<?php echo number_format($item['price'], 2); ?></p>
                            </button>
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
                <template x-if="cart.length === 0">
                    <p class="text-gray-500 text-center py-16">Click an item to add it to the bill.</p>
                </template>
                <template x-for="item in cart" :key="item.id">
                    <div class="flex justify-between items-center mb-3">
                        <div>
                            <p class="font-semibold text-sm" x-text="item.name"></p>
                            <p class="text-xs text-gray-500" x-text="'$' + parseFloat(item.price).toFixed(2)"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="updateQuantity(item.id, item.quantity - 1)" class="w-6 h-6 bg-gray-200 rounded">-</button>
                            <span x-text="item.quantity" class="w-6 text-center"></span>
                            <button @click="updateQuantity(item.id, item.quantity + 1)" class="w-6 h-6 bg-gray-200 rounded">+</button>
                            <span class="font-bold text-sm w-16 text-right" x-text="'$' + (item.price * item.quantity).toFixed(2)"></span>
                        </div>
                    </div>
                </template>
            </div>
            
            <div class="border-t pt-4 mt-4">
                <div class="flex justify-between font-semibold">
                    <span>Subtotal</span>
                    <span x-text="'$' + subtotal.toFixed(2)"></span>
                </div>
                 <div class="flex justify-between font-semibold mt-2">
                    <span>Tax (10%)</span>
                    <span x-text="'$' + tax.toFixed(2)"></span>
                </div>
                <div class="flex justify-between font-bold text-xl mt-2 text-amber-700">
                    <span>Total</span>
                    <span x-text="'$' + total.toFixed(2)"></span>
                </div>
            </div>

            <div class="mt-6">
                <button @click="clearCart()" class="w-full bg-red-500 text-white py-2 rounded-md hover:bg-red-600 transition mb-2">Clear Bill</button>
                <button :disabled="cart.length === 0" class="w-full bg-green-500 text-white py-3 rounded-md hover:bg-green-600 transition disabled:bg-gray-400">Mark as Paid & Complete</button>
            </div>
        </div>
    </div>
</div>

<script>
function posSystem() {
    return {
        cart: [],
        get subtotal() {
            return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
        },
        get tax() {
            return this.subtotal * 0.10; // 10% tax rate
        },
        get total() {
            return this.subtotal + this.tax;
        },
        addToCart(menuItem) {
            const existingItem = this.cart.find(item => item.id === menuItem.id);
            if (existingItem) {
                existingItem.quantity++;
            } else {
                this.cart.push({ ...menuItem, quantity: 1 });
            }
        },
        updateQuantity(id, quantity) {
            const item = this.cart.find(i => i.id === id);
            if (item) {
                if (quantity <= 0) {
                    this.cart = this.cart.filter(i => i.id !== id);
                } else {
                    item.quantity = quantity;
                }
            }
        },
        clearCart() {
            if (confirm('Are you sure you want to clear the entire bill?')) {
                this.cart = [];
            }
        }
    }
}
</script>

<?php require_once 'partials/footer.php'; ?>
