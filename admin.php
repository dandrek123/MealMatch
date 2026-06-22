<?php
session_start();
require_once 'db.php';

// Only admins allowed
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: index.php");
    exit;
}

$errors = [];
$success = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM meals WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $success = "Meal deleted.";
        } else {
            $errors[] = "Could not delete meal.";
        }
    }
}

// Handle add meal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $ingredients= trim($_POST['ingredients'] ?? '');
    $diet_type  = trim($_POST['diet_type'] ?? '');
    $image_path = trim($_POST['image_path'] ?? '');
    $tags       = trim($_POST['tags'] ?? '');

    if ($name === '' || $ingredients === '' || $image_path === '') {
        $errors[] = "Meal name, ingredients, and image path are required.";
    }

    if ($diet_type === '') {
        $diet_type = null;
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "INSERT INTO meals (name, ingredients, diet_type, image_url, tags)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $name, $ingredients, $diet_type, $image_path, $tags);

        if ($stmt->execute()) {
            $success = "Meal added successfully.";
        } else {
            $errors[] = "Could not add meal.";
        }
    }
}

// Load all meals
$meals_rs = $conn->query("SELECT id, name, diet_type, image_url, tags 
                          FROM meals 
                          ORDER BY id ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>MealMatch Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
    <div class="container">
        <span class="navbar-brand">MealMatch Admin</span>
        <div class="ms-auto">
            <span class="me-3">
                Logged in as: <?= htmlspecialchars($_SESSION['username']); ?> (admin)
            </span>
            <a href="index.php" class="btn btn-outline-primary btn-sm me-2">Back to Meals</a>
            <a href="analytics.php" class="btn btn-outline-secondary btn-sm me-2">Analytics</a>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container pb-5">
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

    <h3 class="mb-3">Add New Meal</h3>

    <form method="post" class="card p-3 mb-4 shadow-sm">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label">Meal Name *</label>
                <input type="text" name="name" class="form-control"
                       value="<?= htmlspecialchars($_POST['name'] ?? ''); ?>">
            </div>

            <div class="col-12">
                <label class="form-label">Ingredients * (comma separated)</label>
                <textarea name="ingredients" class="form-control" rows="2"><?= htmlspecialchars($_POST['ingredients'] ?? ''); ?></textarea>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label">Diet Type (e.g., high-protein, vegan, vegetarian)</label>
                <input type="text" name="diet_type" class="form-control"
                       value="<?= htmlspecialchars($_POST['diet_type'] ?? ''); ?>">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label">Image Path * (e.g., images/chicken_salad.jpg)</label>
                <input type="text" name="image_path" class="form-control"
                       value="<?= htmlspecialchars($_POST['image_path'] ?? ''); ?>">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label">Tags (comma separated)</label>
                <input type="text" name="tags" class="form-control"
                       value="<?= htmlspecialchars($_POST['tags'] ?? ''); ?>">
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-success">Add Meal</button>
            </div>
        </div>
    </form>

    <h3 class="mb-3">All Meals</h3>

    <div class="table-responsive card shadow-sm">
        <table class="table table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Diet Type</th>
                    <th>Image</th>
                    <th>Tags</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $meals_rs->fetch_assoc()): ?>
                <tr>
                    <td><?= (int) $row['id']; ?></td>
                    <td><?= htmlspecialchars($row['name']); ?></td>
                    <td><?= htmlspecialchars($row['diet_type']); ?></td>
                    <td><?= htmlspecialchars($row['image_url']); ?></td>
                    <td><?= htmlspecialchars($row['tags']); ?></td>
                    <td>
                        <!-- <a href="admin.php?edit=<?= (int)$row['id']; ?>">Edit</a> -->
                        <a href="admin.php?delete=<?= (int)$row['id']; ?>"
                           onclick="return confirm('Delete this meal?');"
                           class="btn btn-sm btn-outline-danger">
                            Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>