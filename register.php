<?php
require_once 'config/db.php';
$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // --- Validation ---
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = 'An account with this email already exists.';
    }
    $stmt->close();
    
    // --- If no errors, create user ---
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashed_password);
        
        if ($stmt->execute()) {
            // Redirect to login page on success
            header("Location: login.php?registered=success");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again. Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; } .font-display { font-family: 'Playfair Display', serif; }</style>
</head>
<body class="bg-gray-100">
    <div class="flex items-center justify-center min-h-screen">
        <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md">
            <div class="text-center">
                <h1 class="text-3xl font-bold font-display text-gray-900">Create Your Account</h1>
                <p class="mt-2 text-gray-600">Join us and discover delicious food!</p>
            </div>

            <!-- Display Errors -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Oops!</strong>
                    <ul class="mt-2 list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="space-y-6" action="register.php" method="POST">
                <div>
                    <label for="name" class="text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" name="name" id="name" required value="<?php echo htmlspecialchars($name); ?>" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                </div>
                <div>
                    <label for="email" class="text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($email); ?>" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                </div>
                <div>
                    <label for="password" class="text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                </div>
                <div>
                    <label for="confirm_password" class="text-sm font-medium text-gray-700">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                </div>
                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-full shadow-sm text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                        Sign Up
                    </button>
                </div>
            </form>

            <p class="text-sm text-center text-gray-600">
                Already have an account? 
                <a href="login.php" class="font-medium text-amber-600 hover:text-amber-500">
                    Log In
                </a>
            </p>
        </div>
    </div>
</body>
</html>
