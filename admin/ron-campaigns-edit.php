<?php
session_start();
require_once '../includes/auth.php';
require_login();
require_once '../config/database.php';

$id = intval($_GET['id'] ?? 0);

// Get campaign data
$stmt = $conn->prepare("SELECT c.*, u.username AS advertiser, cat.name AS category FROM campaigns c
    LEFT JOIN users u ON c.advertiser_id = u.id
    LEFT JOIN categories cat ON c.category_id = cat.id
    WHERE c.id = ? AND c.type='ron'");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if (!$row = $res->fetch_assoc()) {
    echo "<div class='alert alert-danger m-4'>Campaign not found!</div>";
    exit;
}

// Ambil advertisers
$advertisers = [];
$advq = $conn->query("SELECT id, username FROM users WHERE role='advertiser'");
while ($a = $advq->fetch_assoc()) $advertisers[] = $a;

// Ambil categories
$categories = [];
$catq = $conn->query("SELECT id, name FROM categories");
while ($c = $catq->fetch_assoc()) $categories[] = $c;

// Update campaign
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $advertiser_id = intval($_POST['advertiser_id']);
    $category_id = intval($_POST['category_id']);
    $status = intval($_POST['status']);

    $stmt2 = $conn->prepare("UPDATE campaigns SET name=?, advertiser_id=?, category_id=?, status=? WHERE id=?");
    $stmt2->bind_param('siiii', $name, $advertiser_id, $category_id, $status, $id);
    $stmt2->execute();

    header("Location: ron-campaigns.php?msg=updated");
    exit;
}
?>

<?php include 'templates/header.php'; ?>
<div class="container py-4" style="max-width:600px;">
    <h2 class="mb-4"><i class="bi bi-pencil-square"></i> Edit RON Campaign</h2>
    <form method="post" class="border rounded shadow-sm p-4 bg-white">
        <div class="mb-3">
            <label>Campaign Name</label>
            <input name="name" class="form-control" required value="<?= htmlspecialchars($row['name']) ?>">
        </div>
        <div class="mb-3">
            <label>Advertiser</label>
            <select name="advertiser_id" class="form-select" required>
                <option value="">-- Select --</option>
                <?php foreach ($advertisers as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= $a['id'] == $row['advertiser_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a['username']) ?>
                    </option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Category</label>
            <select name="category_id" class="form-select" required>
                <option value="">-- Select --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $row['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-select">
                <option value="1" <?= $row['status'] ? 'selected' : '' ?>>Active</option>
                <option value="0" <?= !$row['status'] ? 'selected' : '' ?>>Paused</option>
            </select>
        </div>
        <div class="d-flex justify-content-between">
            <a href="ron-campaigns.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Cancel</a>
            <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Save Changes</button>
        </div>
    </form>
</div>
<?php include 'templates/footer.php'; ?>
