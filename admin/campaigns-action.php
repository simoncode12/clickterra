<?php
// File: /admin/campaigns-action.php (UPDATED to save ad_format_id)

require_once __DIR__ . '/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: campaigns.php');
    exit();
}

// Aksi: Membuat Kampanye Baru
if (isset($_POST['create_campaign'])) {
    $ad_format_id = filter_input(INPUT_POST, 'ad_format_id', FILTER_VALIDATE_INT);
    $name = trim($_POST['name']);
    $advertiser_id = filter_input(INPUT_POST, 'advertiser_id', FILTER_VALIDATE_INT);
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    // ... (variabel lain tetap sama)

    if (empty($name) || !$advertiser_id || !$category_id || !$ad_format_id) {
        // ... (redirect dengan pesan error)
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO campaigns (name, advertiser_id, category_id, ad_format_id, serve_on_internal, allow_external_rtb, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param("siiiiis", $name, $advertiser_id, $category_id, $ad_format_id, $serve_on_internal, $allow_external_rtb);
        $stmt->execute();
        $campaign_id = $conn->insert_id;
        // ... (sisa logika try-catch tetap sama)
    } catch (Exception $e) {
        // ...
    }
}

// ... (Blok kode untuk Update dan Delete tetap sama)
?>
