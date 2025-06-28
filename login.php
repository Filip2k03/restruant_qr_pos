<?php
require_once 'config/db.php';
$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // --- Validation ---
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    // --- If no validation errors, check credentials ---
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, start session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: admin/index.php");
                } else {
                    header("Location: menu.php");
                }
                exit();
            } else {
                $errors[] = "Invalid email or password.";
            }
        } else {
            $errors[] = "Invalid email or password.";
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
    <title>Login - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; } .font-display { font-family: 'Playfair Display', serif; }</style>
</head>
<body class="bg-gray-100">
    <div class="flex items-center justify-center min-h-screen">
        <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md">
            <div class="text-center">
                <h1 class="text-3xl font-bold font-display text-gray-900">Welcome Back!</h1>
                <p class="mt-2 text-gray-600">Log in to continue your delicious journey.</p>
            </div>

             <!-- Success Message on Registration -->
            <?php if (isset($_GET['registered']) && $_GET['registered'] === 'success'): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline">Your account has been created. Please log in.</span>
                </div>
            <?php endif; ?>

            <!-- Display Errors -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="space-y-6" action="login.php" method="POST">
                <div>
                    <label for="email" class="text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($email); ?>" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                </div>
                <div>
                    <label for="password" class="text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                </div>
                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-full shadow-sm text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                        Log In
                    </button>
                </div>
            </form>

            <p class="text-sm text-center text-gray-600">
                Don't have an account? 
                <a href="register.php" class="font-medium text-amber-600 hover:text-amber-500">
                    Sign Up
                </a>
            </p>
             <p class="text-sm text-center text-gray-500">
                <a href="index.php" class="hover:underline">← Back to Home</a>
            </p>
        </div>
    </div>
</body>
</html>
