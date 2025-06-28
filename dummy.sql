-- This script populates your database with realistic dummy data.
-- TRUNCATE commands are included to clear existing data before inserting new data.
-- This ensures you can run the script multiple times without creating duplicates.

-- Clear existing data
TRUNCATE TABLE `order_items`;
TRUNCATE TABLE `orders`;
TRUNCATE TABLE `cart`;
TRUNCATE TABLE `coupons`;
TRUNCATE TABLE `menu_items`;
TRUNCATE TABLE `categories`;
TRUNCATE TABLE `tables`;
TRUNCATE TABLE `users`;

--
-- Inserting data for table `categories`
--
INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Artisanal Coffee', 'Expertly brewed coffee from locally sourced beans.'),
(2, 'Tea & Refreshers', 'A selection of local teas, fresh juices, and smoothies.'),
(3, 'Pastries & Snacks', 'Flaky croissants, savory puffs, and light bites.'),
(4, 'Cakes & Desserts', 'Indulgent, sweet treats to complete your meal.'),
(5, 'Savory Meals', 'Hearty and delicious meals, perfect for breakfast or lunch.');

--
-- Inserting data for table `menu_items`
--
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image_url`, `is_available`) VALUES
-- Coffee
(1, 1, 'Classic Espresso', 'A concentrated coffee beverage brewed by forcing hot water under pressure through finely-ground coffee beans.', 2.50, 'https://placehold.co/400x300/6f4e37/ffffff?text=Espresso', 1),
(2, 1, 'Americano', 'Espresso shots topped with hot water create a light layer of crema.', 3.00, 'https://placehold.co/400x300/8B4513/ffffff?text=Americano', 1),
(3, 1, 'Cafe Latte', 'Rich espresso with steamed milk, topped with a thin layer of foam.', 4.00, 'https://placehold.co/400x300/c2a28a/ffffff?text=Latte', 1),
(4, 1, 'Caramel Macchiato', 'Freshly steamed milk with vanilla-flavored syrup, marked with espresso and topped with a caramel drizzle.', 4.50, 'https://placehold.co/400x300/D2B48C/333?text=Macchiato', 1),

-- Tea & Refreshers
(5, 2, 'Myanmar Milk Tea (Laphet Yay)', 'A strong, sweet, and creamy local favorite, made with black tea and condensed milk.', 2.00, 'https://placehold.co/400x300/d1a377/ffffff?text=Milk+Tea', 1),
(6, 2, 'Iced Lemon Tea', 'A refreshing blend of black tea, lemon juice, and a hint of sweetness, served over ice.', 3.00, 'https://placehold.co/400x300/FFD700/333?text=Iced+Tea', 1),
(7, 2, 'Avocado Smoothie', 'Creamy, rich, and nutritious smoothie made from fresh local avocados.', 4.00, 'https://placehold.co/400x300/98FB98/333?text=Avocado', 1),
(8, 2, 'Fresh Sugarcane Juice (Kyann Yay)', 'A sweet and revitalizing juice, pressed fresh to order.', 2.50, 'https://placehold.co/400x300/c8e6c9/333?text=Sugarcane', 1),

-- Pastries & Snacks
(9, 3, 'Butter Croissant', 'A classic, flaky, and buttery French pastry, baked fresh daily.', 3.00, 'https://placehold.co/400x300/e6c8a0/333?text=Croissant', 1),
(10, 3, 'Samosa Salad', 'Crispy vegetable samosas served on a bed of fresh greens with a tangy tamarind dressing.', 4.50, 'https://placehold.co/400x300/f5deb3/333?text=Samosa', 1),
(11, 3, 'Chicken Puff', 'A savory puff pastry filled with a mildly spiced chicken mixture.', 3.50, 'https://placehold.co/400x300/f4a460/333?text=Puff', 0),

-- Cakes & Desserts
(12, 4, 'New York Cheesecake', 'A rich and creamy cheesecake with a graham cracker crust, served with a berry compote.', 6.00, 'https://placehold.co/400x300/fff8dc/333?text=Cheesecake', 1),
(13, 4, 'Molten Chocolate Lava Cake', 'A decadent chocolate cake with a gooey, liquid chocolate center, served warm.', 6.50, 'https://placehold.co/400x300/5C4033/ffffff?text=Lava+Cake', 1),

-- Savory Meals
(14, 5, 'Avocado & Egg Toast', 'Toasted sourdough topped with smashed avocado, a perfectly poached egg, and chili flakes.', 7.00, 'https://placehold.co/400x300/b5e7a0/333?text=Avocado+Toast', 1),
(15, 5, 'Classic Club Sandwich', 'A triple-decker sandwich with grilled chicken, bacon, lettuce, tomato, and mayonnaise.', 8.00, 'https://placehold.co/400x300/deb887/333?text=Sandwich', 1),
(16, 5, 'Shan Noodle Salad', 'A popular local dish with rice noodles, marinated chicken, and a mix of fresh herbs and crushed peanuts.', 5.50, 'https://placehold.co/400x300/f0e68c/333?text=Shan+Noodles', 1);

--
-- Inserting data for table `users`
-- Note: The password for all users is 'password123'. The password is 'hashed' for security.
--
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Admin User', 'admin@example.com', '$2y$10$I0jJ.7M8t8/dJ.Z.YQ82XuzZ8aC2pPj3OaDq0r7Pz.N.eU3b1fB.O', 'admin'),
(2, 'Aung Aung', 'aung.aung@example.com', '$2y$10$I0jJ.7M8t8/dJ.Z.YQ82XuzZ8aC2pPj3OaDq0r7Pz.N.eU3b1fB.O', 'user'),
(3, 'Su Su', 'su.su@example.com', '$2y$10$I0jJ.7M8t8/dJ.Z.YQ82XuzZ8aC2pPj3OaDq0r7Pz.N.eU3b1fB.O', 'user');

--
-- Inserting data for table `tables`
--
INSERT INTO `tables` (`id`, `table_number`, `status`) VALUES
(1, 'A1 (Window)', 'free'),
(2, 'A2 (Window)', 'in_use'),
(3, 'B1 (Cozy Corner)', 'free'),
(4, 'B2 (Cozy Corner)', 'free'),
(5, 'C1 (Garden View)', 'needs_cleaning'),
(6, 'C2 (Garden View)', 'free');

--
-- Inserting data for table `coupons`
--
INSERT INTO `coupons` (`id`, `code`, `discount_type`, `discount_value`, `usage_limit`, `expiry_date`, `is_active`) VALUES
(1, 'MMRNEW25', 'percentage', 25.00, 100, '2025-07-31', 1),
(2, '5BUCKS', 'fixed', 5.00, 50, '2025-12-31', 1),
(3, 'LUNCHDEAL', 'percentage', 15.00, 200, '2025-05-31', 1), -- Expired
(4, 'INACTIVE', 'fixed', 10.00, 10, '2025-12-31', 0); -- Inactive

