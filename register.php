<?php
session_start();
require_once 'db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Basic validation
    if ($username === '' || $password === '' || $confirm === '') {
        $errors[] = "Username and both password fields are required.";
    }

    if (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address, or leave it blank.";
    }

    // Check for existing username
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $rs = $stmt->get_result();

        if ($rs->num_rows > 0) {
            $errors[] = "That username is already taken.";
        }
    }

    // Insert user
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role) 
                                VALUES (?, ?, ?, 'user')");
        $stmt->bind_param("sss", $username, $email, $hash);

        if ($stmt->execute()) {
            $success = "Account created. You can now log in.";
        } else {
            $errors[] = "Something went wrong creating your account.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>MealMatch – Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-4">
            <h2 class="text-center mb-4">Create an Account</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e): ?>
                        <div><?= htmlspecialchars($e); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="post" class="card p-3 shadow-sm">
                <div class="mb-3">
                    <label class="form-label" for="username">Username *</label>
                    <input class="form-control" type="text" id="username" name="username"
                           value="<?= htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="email">Email (optional)</label>
                    <input class="form-control" type="email" id="email" name="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Password *</label>
                    <input class="form-control" type="password" id="password" name="password">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="confirm_password">Confirm Password *</label>
                    <input class="form-control" type="password" id="confirm_password" name="confirm_password">
                </div>

                <button class="btn btn-success w-100" type="submit">Register</button>

                <p class="mt-3 mb-0 text-center">
                    Already have an account? <a href="login.php">Login</a>
                </p>
            </form>
        </div>
    </div>
</div>

</body>
</html>