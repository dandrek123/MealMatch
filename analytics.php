<?php
session_start();
require_once 'db.php';

// Only admins may access
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit;
}

// Fetch counts
$totalUsers  = $conn->query("SELECT COUNT(*) AS n FROM users")->fetch_assoc()['n'];
$totalMeals  = $conn->query("SELECT COUNT(*) AS n FROM meals")->fetch_assoc()['n'];
$totalAdmins = $conn->query("SELECT COUNT(*) AS n FROM users WHERE role='admin'")->fetch_assoc()['n'];

// Recent users
$recent = $conn->query("SELECT username, created_at 
                        FROM users 
                        ORDER BY created_at DESC 
                        LIMIT 5");

// Tag analytics
$tags = [];
$tagRs = $conn->query("SELECT tags FROM meals");
while ($row = $tagRs->fetch_assoc()) {
    $parts = array_map('trim', explode(',', $row['tags']));
    foreach ($parts as $t) {
        if ($t !== '') {
            $tags[$t] = ($tags[$t] ?? 0) + 1;
        }
    }
}
arsort($tags);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Analytics Dashboard</title>
    <!-- Bootstrap first, then custom CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .stat-number {
            font-size: 2.2rem;
            font-weight: 600;
            margin: 0;
        }
        .section-title {
            margin-bottom: 0.75rem;
        }
    </style>
</head>
<body>

<nav class="py-2 mb-4 bg-light border-bottom">
    <div class="container d-flex flex-wrap justify-content-center gap-3 small">
        <span class="fw-semibold">
            Welcome, <?= htmlspecialchars($_SESSION['username']); ?>
        </span>

        <a href="index.php" class="text-decoration-none">Meals</a>
        <a href="admin.php" class="text-decoration-none">Admin Panel</a>
        <a href="logout.php" class="text-decoration-none text-danger fw-semibold">Logout</a>
    </div>
</nav>

<div class="container my-4">

    <h2 class="text-center mb-4">Analytics Dashboard</h2>

    <!-- Top stats cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card h-100 py-3 d-flex align-items-center justify-content-center">
                <h5 class="card-title mb-2">Total Users</h5>
                <p class="stat-number"><?= $totalUsers ?></p>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card h-100 py-3 d-flex align-items-center justify-content-center">
                <h5 class="card-title mb-2">Total Meals</h5>
                <p class="stat-number"><?= $totalMeals ?></p>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card h-100 py-3 d-flex align-items-center justify-content-center">
                <h5 class="card-title mb-2">Total Admins</h5>
                <p class="stat-number"><?= $totalAdmins ?></p>
            </div>
        </div>
    </div>

    <!-- Recent users -->
    <div class="card mb-4">
        <div class="card-body">
            <h4 class="section-title">Most Recent Users</h4>
            <div class="table-responsive mt-2">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($u = $recent->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= $u['created_at'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tags -->
    <div class="card mb-4">
        <div class="card-body">
            <h4 class="section-title">Most Popular Meal Tags</h4>
            <div class="table-responsive mt-2">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tag</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($tags as $tag => $count): ?>
                        <tr>
                            <td><?= htmlspecialchars($tag) ?></td>
                            <td><?= $count ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

</body>
</html>