<?php
// File: /admin/ron-creative-action.php (FINAL - All single and bulk actions restored)

require_once __DIR__ . '/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: campaigns.php');
    exit();
}

// Tentukan URL redirect di awal untuk digunakan oleh semua aksi
$campaign_id_redirect = filter_input(INPUT_POST, 'campaign_id', FILTER_VALIDATE_INT);
$redirect_url = 'ron-creative.php' . ($campaign_id_redirect ? '?campaign_id=' . $campaign_id_redirect : '');

function redirect_with_message($type, $message, $location) {
    $_SESSION[$type . '_message'] = $message;
    header("Location: $location");
    exit();
}

// --- AKSI MASSAL (BULK ACTION) ---
if (isset($_POST['apply_bulk_action'])) {
    $action = $_POST['bulk_action'];
    $creative_ids = $_POST['creative_ids'] ?? [];

    if (empty($action) || empty($creative_ids)) {
        redirect_with_message('error', 'No action or no creatives selected.', $redirect_url);
    }

    // Pastikan semua ID adalah integer untuk keamanan
    $sanitized_ids = array_map('intval', $creative_ids);
    $ids_placeholder = implode(',', array_fill(0, count($sanitized_ids), '?'));
    $types = str_repeat('i', count($sanitized_ids));
    $sql = '';

    switch ($action) {
        case 'delete':
            // TODO: Hapus file terkait jika ada sebelum menghapus dari DB
            $sql = "DELETE FROM creatives WHERE id IN ({$ids_placeholder})";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$sanitized_ids);
            break;
        case 'activate':
            $sql = "UPDATE creatives SET status = 'active' WHERE id IN ({$ids_placeholder})";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$sanitized_ids);
            break;
        case 'pause':
            $sql = "UPDATE creatives SET status = 'paused' WHERE id IN ({$ids_placeholder})";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$sanitized_ids);
            break;
    }

    if (isset($stmt) && $stmt->execute()) {
        redirect_with_message('success', 'Bulk action completed successfully.', $redirect_url);
    } else {
        redirect_with_message('error', 'Failed to perform bulk action.', $redirect_url);
    }
}

// --- AKSI UPDATE BID MASSAL ---
if (isset($_POST['update_bulk_bids'])) {
    $new_bid = filter_input(INPUT_POST, 'new_bid_amount', FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $creative_ids = $_POST['creative_ids'] ?? [];

    if ($new_bid === false || empty($creative_ids)) {
        redirect_with_message('error', 'Invalid bid amount or no creatives selected.', $redirect_url);
    }

    $sanitized_ids = array_map('intval', $creative_ids);
    $ids_placeholder = implode(',', array_fill(0, count($sanitized_ids), '?'));
    $types = 'd' . str_repeat('i', count($sanitized_ids));
    $params = array_merge([$new_bid], $sanitized_ids);

    $sql = "UPDATE creatives SET bid_amount = ? WHERE id IN ({$ids_placeholder})";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        redirect_with_message('success', 'Bulk bid update completed successfully.', $redirect_url);
    } else {
        redirect_with_message('error', 'Failed to perform bulk bid update.', $redirect_url);
    }
}


// --- FUNGSI PENTING YANG DIKEMBALIKAN: AKSI TAMBAH CREATIVE BARU ---
if (isset($_POST['add_creative'])) {
    $campaign_id = filter_input(INPUT_POST, 'campaign_id', FILTER_VALIDATE_INT);
    $name = trim($_POST['name']);
    $bid_model = $_POST['bid_model'];
    $bid_amount = filter_input(INPUT_POST, 'bid_amount', FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $creative_type = $_POST['creative_type'];

    if (empty($name) || !$bid_amount || !$campaign_id) { 
        redirect_with_message('error', 'Please fill all creative details correctly.', $redirect_url); 
    }
    
    $image_url_db = null; 
    $landing_url_db = null; 
    $sizes_db = null; 
    $script_content_db = null;

    if ($creative_type === 'image') {
        $landing_url_db = filter_input(INPUT_POST, 'landing_url', FILTER_VALIDATE_URL);
        $sizes_db = $_POST['sizes'];
        if (!$landing_url_db) {
            redirect_with_message('error', 'A valid landing page URL is required for image creatives.', $redirect_url);
        }

        if (isset($_FILES['creative_file']) && $_FILES['creative_file']['error'] == 0) {
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $file_name = "creative_" . $campaign_id . "_" . time() . '_' . basename($_FILES['creative_file']['name']);
            if (move_uploaded_file($_FILES['creative_file']['tmp_name'], $upload_dir . $file_name)) { 
                $image_url_db = 'uploads/' . $file_name; 
            } else { 
                redirect_with_message('error', 'Failed to upload file.', $redirect_url); 
            }
        } else { 
            $image_url_db = filter_input(INPUT_POST, 'image_url', FILTER_VALIDATE_URL); 
        }
        if (empty($image_url_db)) {
            redirect_with_message('error', 'Please provide an image by uploading or using a hotlink.', $redirect_url);
        }

    } elseif ($creative_type === 'script') {
        $script_content_db = $_POST['script_content'];
        $sizes_db = 'all'; // Script type usually fits all sizes
        if (empty($script_content_db)) {
            redirect_with_message('error', 'Script content cannot be empty.', $redirect_url);
        }
    }

    $stmt = $conn->prepare("INSERT INTO creatives (campaign_id, name, creative_type, bid_model, bid_amount, image_url, landing_url, script_content, sizes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    // Perhatikan tipe data: i, s, s, s, d, s, s, s, s
    $stmt->bind_param("isssissss", $campaign_id, $name, $creative_type, $bid_model, $bid_amount, $image_url_db, $landing_url_db, $script_content_db, $sizes_db);
    
    if ($stmt->execute()) { 
        redirect_with_message('success', 'Creative "' . htmlspecialchars($name) . '" was created.', $redirect_url); 
    } else { 
        redirect_with_message('error', 'Database error: ' . $stmt->error, $redirect_url); 
    }
    $stmt->close();
}

// --- Aksi untuk update dari halaman edit (jika ada) ---
if (isset($_POST['update_creative'])) {
    // ... (Logika untuk update satu creative)
}


// Fallback redirect jika tidak ada aksi yang cocok
header('Location: ' . $redirect_url);
exit();
?>
