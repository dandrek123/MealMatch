<?php
session_start();
require_once 'db.php';

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$tag = trim($_GET['tag'] ?? '');
?>
<!DOCTYPE html>
<html>
<head>
    <title>MealMatch – Matches</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body class="p-3">

<nav class="py-2 mb-4 bg-light border-bottom">
    <div class="container d-flex flex-wrap justify-content-center gap-3 small">

        <span class="fw-semibold">
            Welcome, <?= htmlspecialchars($_SESSION['username']); ?>
        </span>

        <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin.php" class="text-decoration-none">Admin Panel</a>
            <a href="analytics.php" class="text-decoration-none">Analytics</a>
        <?php endif; ?>

        <a href="index.php" class="text-decoration-none">All Meals</a>

        <a href="logout.php" class="text-decoration-none text-danger fw-semibold">
            Logout
        </a>
    </div>
</nav>

<h2 class="mt-3 mb-3 text-center">
    Matches
    <?php if ($tag): ?>
        for "<?= htmlspecialchars($tag); ?>"
    <?php endif; ?>
</h2>

<form method="get" class="mb-4">
    <div class="d-flex justify-content-center">
        <div class="input-group" style="max-width: 400px;">
            <input
                type="text"
                name="tag"
                class="form-control"
                placeholder="Filter by tag (e.g., high-protein, vegan)"
                value="<?= htmlspecialchars($tag); ?>"
            >
            <button class="btn btn-primary" type="submit">Filter</button>
        </div>
    </div>
</form>

<div class="container mt-3">
    <div class="row g-3">
        <?php
        if ($tag !== '') {
            $like = "%$tag%";
            $stmt = $conn->prepare(
                "SELECT name, image_url, ingredients 
                 FROM meals 
                 WHERE tags LIKE ?
                 ORDER BY name ASC"
            );
            $stmt->bind_param("s", $like);
            $stmt->execute();
            $res = $stmt->get_result();
        } else {
            $res = $conn->query(
                "SELECT name, image_url, ingredients 
                 FROM meals 
                 ORDER BY name ASC"
            );
        }

        if ($res->num_rows === 0) {
            echo "<p class='text-center text-muted'>No meals found for this tag.</p>";
        }

        while ($row = $res->fetch_assoc()) {
            $name = htmlspecialchars($row['name']);
            $img  = htmlspecialchars($row['image_url']);
            $ing  = htmlspecialchars($row['ingredients']);

            echo "
            <div class='col-12 col-sm-6 col-md-4 col-lg-3 mb-4'>
                <div class='card meal-card h-100'>
                    <img src='$img' class='card-img-top' alt='$name'>
                    <div class='card-body'>
                        <h5 class='card-title'>$name</h5>
                        <p class='card-text'>$ing</p>
                    </div>
                </div>
            </div>";
        }
        ?>
    </div>
</div>

</body>
</html>