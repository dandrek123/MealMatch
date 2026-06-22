<?php
session_start();
require_once 'db.php';

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Read filters from query string
$q    = trim($_GET['q']    ?? '');
$diet = trim($_GET['diet'] ?? '');
?>
<!DOCTYPE html>
<html>
<head>
  <title>MealMatch</title>

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

        <a href="match.php" class="text-decoration-none">Tag Matches</a>

        <a href="logout.php" class="text-decoration-none text-danger fw-semibold">
            Logout
        </a>
    </div>
</nav>

<h2 class="mt-3 mb-2 text-center">Meals</h2>

<p class="text-center text-muted small mb-4">
    MealMatch recommends dishes by matching ingredients you enter with meals in the database.
    Whether you're planning dinners, exploring new diet types, or just using what’s in the fridge,
    MealMatch finds the best options for you.
</p>

<!-- Ingredient search -->
<form method="get" class="mb-3">
    <div class="d-flex justify-content-center">
        <div class="input-group" style="max-width: 400px;">
            <input
                type="text"
                name="q"
                class="form-control"
                placeholder="Search by ingredient..."
                value="<?= htmlspecialchars($q); ?>">
            <!-- Keep current diet filter when searching -->
            <?php if ($diet !== ''): ?>
                <input type="hidden" name="diet" value="<?= htmlspecialchars($diet); ?>">
            <?php endif; ?>
            <button class="btn btn-primary" type="submit">Search</button>
        </div>
    </div>
</form>

<!-- Diet filter buttons -->
<div class="d-flex justify-content-center mb-4">
    <div class="btn-group" role="group" aria-label="Diet filters">
        <?php
        // Helper to set active class
        function dietBtnClass($current, $value) {
            return $current === $value ? 'btn btn-sm btn-primary' : 'btn btn-sm btn-outline-primary';
        }
        ?>

        <a href="index.php<?= $q !== '' ? '?q=' . urlencode($q) : '' ?>"
           class="<?= dietBtnClass($diet, ''); ?>">
            All
        </a>

        <a href="index.php?diet=high-protein<?= $q !== '' ? '&q=' . urlencode($q) : '' ?>"
           class="<?= dietBtnClass($diet, 'high-protein'); ?>">
            High-protein
        </a>

        <a href="index.php?diet=vegan<?= $q !== '' ? '&q=' . urlencode($q) : '' ?>"
           class="<?= dietBtnClass($diet, 'vegan'); ?>">
            Vegan
        </a>

        <a href="index.php?diet=vegetarian<?= $q !== '' ? '&q=' . urlencode($q) : '' ?>"
           class="<?= dietBtnClass($diet, 'vegetarian'); ?>">
            Vegetarian
        </a>
    </div>
</div>

<div class="container mt-3">
  <div class="row g-3">
    <?php
    // Build dynamic query based on filters
    $sql        = "SELECT name, image_url, ingredients FROM meals";
    $conditions = [];
    $params     = [];
    $types      = '';

    if ($q !== '') {
        $conditions[] = "ingredients LIKE ?";
        $params[]     = "%$q%";
        $types       .= "s";
    }

    if ($diet !== '') {
        $conditions[] = "diet_type = ?";
        $params[]     = $diet;
        $types       .= "s";
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $sql .= " ORDER BY name ASC";

    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rs = $stmt->get_result();
    } else {
        $rs = $conn->query($sql);
    }

    if ($rs->num_rows === 0) {
        echo "<p class='text-center text-muted'>No meals found for this search/filter.</p>";
    }

    while ($row = $rs->fetch_assoc()) {

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