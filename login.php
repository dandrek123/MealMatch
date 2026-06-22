<?php
session_start();
require_once 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = "Username and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password_hash, role 
                                FROM users 
                                WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $rs = $stmt->get_result();

        if ($user = $rs->fetch_assoc()) {
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header("Location: index.php");
                exit;
            } else {
                $errors[] = "Invalid username or password.";
            }
        } else {
            $errors[] = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>MealMatch – Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-4">
            <h2 class="text-center mb-4">MealMatch Login</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e): ?>
                        <div><?= htmlspecialchars($e); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" class="card p-3 shadow-sm">
                <div class="mb-3">
                    <label class="form-label" for="username">Username</label>
                    <input class="form-control" type="text" id="username" name="username"
                           value="<?= htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-control" type="password" id="password" name="password">
                </div>

                <button class="btn btn-primary w-100" type="submit">Login</button>

                <p class="mt-3 mb-0 text-center">
                    Don’t have an account? <a href="register.php">Register</a>
                </p>
            </form>
        </div>
    </div>
</div>

</body>
</html>