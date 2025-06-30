<?php
// File: /admin/ron-creative-action.php (FULL CODE with UPDATE logic)

require_once __DIR__ . '/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ron-campaigns.php');
    exit();
}

function redirect_with_message($type, $message, $location) {
    $_SESSION[$type . '_message'] = $message;
    header("Location: $location");
    exit();
}

// AKSI 1: Tambah Creative Baru
if (isset($_POST['add_creative'])) {
    $campaign_id = filter_input(INPUT_POST, 'campaign_id', FILTER_VALIDATE_INT);
    if (!$campaign_id) { redirect_with_message('error', 'Campaign ID is missing.', 'ron-campaigns.php'); }
    
    $name = trim($_POST['name']);
    $bid_model = $_POST['bid_model'];
    $bid_amount = filter_input(INPUT_POST, 'bid_amount', FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $creative_type = $_POST['creative_type'];

    if (empty($name) || !$bid_amount) { redirect_with_message('error', 'Please fill all bid details correctly.', 'ron-creative.php?campaign_id=' . $campaign_id); }
    
    $image_url_db = null; $landing_url_db = null; $sizes_db = null; $script_content_db = null;

    if ($creative_type === 'image') {
        $landing_url_db = filter_input(INPUT_POST, 'landing_url', FILTER_VALIDATE_URL);
        $sizes_db = $_POST['sizes'];
        if (!$landing_url_db) redirect_with_message('error', 'A valid landing page URL is required.', 'ron-creative.php?campaign_id=' . $campaign_id);

        if (isset($_FILES['creative_file']) && $_FILES['creative_file']['error'] == 0) {
            $upload_dir = __DIR__ . '/uploads/';
            $file_name = time() . '_' . basename($_FILES['creative_file']['name']);
            if (move_uploaded_file($_FILES['creative_file']['tmp_name'], $upload_dir . $file_name)) { $image_url_db = 'uploads/' . $file_name; } 
            else { redirect_with_message('error', 'Failed to upload file.', 'ron-creative.php?campaign_id=' . $campaign_id); }
        } else { $image_url_db = filter_input(INPUT_POST, 'image_url', FILTER_VALIDATE_URL); }
        if (empty($image_url_db)) redirect_with_message('error', 'Please provide an image.', 'ron-creative.php?campaign_id=' . $campaign_id);

    } elseif ($creative_type === 'script') {
        $script_content_db = $_POST['script_content'];
        $sizes_db = 'all';
        if (empty($script_content_db)) redirect_with_message('error', 'Script content cannot be empty.', 'ron-creative.php?campaign_id=' . $campaign_id);
    }

    $stmt = $conn->prepare("INSERT INTO creatives (campaign_id, name, creative_type, bid_model, bid_amount, image_url, landing_url, script_content, sizes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssdsdss", $campaign_id, $name, $creative_type, $bid_model, $bid_amount, $image_url_db, $landing_url_db, $script_content_db, $sizes_db);
    
    if ($stmt->execute()) { redirect_with_message('success', 'Creative "' . htmlspecialchars($name) . '" was created.', 'ron-creative.php?campaign_id=' . $campaign_id); } 
    else { redirect_with_message('error', 'Database error: ' . $stmt->error, 'ron-creative.php?campaign_id=' . $campaign_id); }
    $stmt->close();
}


// AKSI 2: Update Creative (Logika Lengkap)
if (isset($_POST['update_creative'])) {
    $creative_id = filter_input(INPUT_POST, 'creative_id', FILTER_VALIDATE_INT);
    $campaign_id = filter_input(INPUT_POST, 'campaign_id', FILTER_VALIDATE_INT);
    $name = trim($_POST['name']);
    $bid_model = $_POST['bid_model'];
    $bid_amount = filter_input(INPUT_POST, 'bid_amount', FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $creative_type = $_POST['creative_type']; // Tipe tidak bisa diubah, hanya dibaca

    if (!$creative_id || !$campaign_id || empty($name)) { redirect_with_message('error', 'Invalid data.', 'ron-creative.php?campaign_id=' . $campaign_id); }
    
    $redirect_url = 'ron-creative-edit.php?id=' . $creative_id;
    
    if ($creative_type === 'image') {
        $landing_url = filter_input(INPUT_POST, 'landing_url', FILTER_VALIDATE_URL);
        $sizes = $_POST['sizes'];
        $image_url = filter_input(INPUT_POST, 'image_url', FILTER_VALIDATE_URL);

        // Jika ada file baru yang di-upload
        if (isset($_FILES['creative_file']) && $_FILES['creative_file']['error'] == 0) {
            // Hapus file lama jika ada
            $stmt_get = $conn->prepare("SELECT image_url FROM creatives WHERE id = ?");
            $stmt_get->bind_param("i", $creative_id); $stmt_get->execute();
            $old_creative = $stmt_get->get_result()->fetch_assoc();
            if ($old_creative && strpos($old_creative['image_url'], 'uploads/') === 0) {
                if (file_exists(__DIR__ . '/' . $old_creative['image_url'])) { unlink(__DIR__ . '/' . $old_creative['image_url']); }
            }
            $stmt_get->close();
            // Upload file baru
            $upload_dir = __DIR__ . '/uploads/';
            $file_name = time() . '_' . basename($_FILES['creative_file']['name']);
            if (move_uploaded_file($_FILES['creative_file']['tmp_name'], $upload_dir . $file_name)) { $image_url = 'uploads/' . $file_name; } 
            else { redirect_with_message('error', 'Failed to upload new file.', $redirect_url); }
        }

        $stmt = $conn->prepare("UPDATE creatives SET name=?, bid_model=?, bid_amount=?, image_url=?, landing_url=?, sizes=? WHERE id=?");
        $stmt->bind_param("ssdsssi", $name, $bid_model, $bid_amount, $image_url, $landing_url, $sizes, $creative_id);

    } else { // script
        $script_content = $_POST['script_content'];
        $stmt = $conn->prepare("UPDATE creatives SET name=?, bid_model=?, bid_amount=?, script_content=? WHERE id=?");
        $stmt->bind_param("ssdsi", $name, $bid_model, $bid_amount, $script_content, $creative_id);
    }
    
    if ($stmt->execute()) { redirect_with_message('success', 'Creative updated successfully.', 'ron-creative.php?campaign_id=' . $campaign_id); } 
    else { redirect_with_message('error', 'Failed to update creative: ' . $stmt->error, $redirect_url); }
    $stmt->close();
}


// AKSI 3: Ubah Status Creative
if (isset($_POST['update_creative_status'])) {
    $creative_id = filter_input(INPUT_POST, 'creative_id', FILTER_VALIDATE_INT);
    $campaign_id = filter_input(INPUT_POST, 'campaign_id', FILTER_VALIDATE_INT);
    $current_status = $_POST['current_status'];

    if (!$creative_id || !$campaign_id) { redirect_with_message('error', 'Invalid ID.', 'ron-campaigns.php'); }
    
    $new_status = ($current_status === 'active') ? 'paused' : 'active';
    $stmt = $conn->prepare("UPDATE creatives SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $creative_id);

    if ($stmt->execute()) { redirect_with_message('success', 'Creative status changed.', 'ron-creative.php?campaign_id=' . $campaign_id); } 
    else { redirect_with_message('error', 'Failed to update status: ' . $stmt->error, 'ron-creative.php?campaign_id=' . $campaign_id); }
    $stmt->close();
}


// AKSI 4: Hapus Creative
if (isset($_POST['delete_creative'])) {
    $creative_id = filter_input(INPUT_POST, 'creative_id', FILTER_VALIDATE_INT);
    $campaign_id = filter_input(INPUT_POST, 'campaign_id', FILTER_VALIDATE_INT);

    if (!$creative_id || !$campaign_id) { redirect_with_message('error', 'Invalid ID.', 'ron-campaigns.php'); }
    
    $stmt_get = $conn->prepare("SELECT image_url FROM creatives WHERE id = ?");
    $stmt_get->bind_param("i", $creative_id); $stmt_get->execute();
    $res = $stmt_get->get_result()->fetch_assoc();
    if ($res && !empty($res['image_url']) && strpos($res['image_url'], 'uploads/') === 0) {
        if (file_exists(__DIR__ . '/' . $res['image_url'])) { unlink(__DIR__ . '/' . $res['image_url']); }
    }
    $stmt_get->close();

    $stmt_delete = $conn->prepare("DELETE FROM creatives WHERE id = ?");
    $stmt_delete->bind_param("i", $creative_id);

    if ($stmt_delete->execute()) { redirect_with_message('success', 'Creative has been deleted.', 'ron-creative.php?campaign_id=' . $campaign_id); } 
    else { redirect_with_message('error', 'Failed to delete creative: ' . $stmt_delete->error, 'ron-creative.php?campaign_id=' . $campaign_id); }
    $stmt_delete->close();
}


// Fallback redirect
header('Location: ron-campaigns.php');
exit();
?>
