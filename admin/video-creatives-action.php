<?php
// File: /admin/video-creatives-action.php (FINAL & COMPLETE - All functions included)

require_once __DIR__ . '/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: campaigns.php');
    exit();
}

$campaign_id_redirect = filter_input(INPUT_POST, 'campaign_id', FILTER_VALIDATE_INT);
$redirect_url = 'video-creatives.php' . ($campaign_id_redirect ? '?campaign_id=' . $campaign_id_redirect : '');

function redirect_with_message($type, $message, $location) {
    $_SESSION[$type . '_message'] = $message;
    header("Location: $location");
    exit();
}

// --- AKSI MASSAL (dari dropdown) ---
if (isset($_POST['apply_bulk_action'])) {
    $action = $_POST['bulk_action'];
    $creative_ids = $_POST['creative_ids'] ?? [];
    if (empty($action) || empty($creative_ids)) {
        redirect_with_message('error', 'No action or no creatives selected.', $redirect_url);
    }

    $sanitized_ids = array_map('intval', $creative_ids);
    $ids_placeholder = implode(',', array_fill(0, count($sanitized_ids), '?'));
    $types = str_repeat('i', count($sanitized_ids));
    $sql = '';

    switch ($action) {
        case 'delete':
            $select_stmt = $conn->prepare("SELECT video_url FROM video_creatives WHERE vast_type = 'upload' AND id IN ({$ids_placeholder})");
            $select_stmt->bind_param($types, ...$sanitized_ids);
            $select_stmt->execute();
            $results = $select_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            foreach ($results as $row) {
                if (!empty($row['video_url']) && file_exists(__DIR__ . '/' . $row['video_url'])) {
                    unlink(__DIR__ . '/' . $row['video_url']);
                }
            }
            $select_stmt->close();
            
            $sql = "DELETE FROM video_creatives WHERE id IN ({$ids_placeholder})";
            break;
        case 'activate':
            $sql = "UPDATE video_creatives SET status = 'active' WHERE id IN ({$ids_placeholder})";
            break;
        case 'pause':
            $sql = "UPDATE video_creatives SET status = 'paused' WHERE id IN ({$ids_placeholder})";
            break;
    }

    if (!empty($sql)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$sanitized_ids);
        if ($stmt->execute()) {
            redirect_with_message('success', 'Bulk action completed successfully.', $redirect_url);
        }
    }
    redirect_with_message('error', 'Failed to perform bulk action.', $redirect_url);
}

// --- AKSI UPDATE LANDING PAGE MASSAL ---
if (isset($_POST['update_bulk_landing_url'])) {
    $new_landing_url = filter_input(INPUT_POST, 'new_landing_url', FILTER_VALIDATE_URL);
    $creative_ids = $_POST['creative_ids'] ?? [];

    if (!$new_landing_url || empty($creative_ids)) {
        redirect_with_message('error', 'Invalid Landing Page URL or no creatives selected.', $redirect_url);
    }
    
    $sanitized_ids = array_map('intval', $creative_ids);
    $ids_placeholder = implode(',', array_fill(0, count($sanitized_ids), '?'));
    $types = 's' . str_repeat('i', count($sanitized_ids));
    $params = array_merge([$new_landing_url], $sanitized_ids);

    $sql = "UPDATE video_creatives SET landing_url = ? WHERE id IN ({$ids_placeholder}) AND vast_type != 'third_party'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        redirect_with_message('success', 'Bulk Landing Page URL update completed successfully for applicable creatives.', $redirect_url);
    } else {
        redirect_with_message('error', 'Failed to perform bulk update: ' . $stmt->error, $redirect_url);
    }
}

// --- AKSI UPDATE SATU CREATIVE DARI HALAMAN EDIT ---
if (isset($_POST['update_video_creative'])) {
    $creative_id = filter_input(INPUT_POST, 'creative_id', FILTER_VALIDATE_INT);
    $name = trim($_POST['name']);
    $bid_model = $_POST['bid_model'];
    $bid_amount = filter_input(INPUT_POST, 'bid_amount', FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $landing_url = filter_input(INPUT_POST, 'landing_url', FILTER_VALIDATE_URL) ?: NULL;
    $duration = filter_input(INPUT_POST, 'duration', FILTER_VALIDATE_INT);
    $status = $_POST['status'];
    $impression_tracker = filter_input(INPUT_POST, 'impression_tracker', FILTER_VALIDATE_URL) ?: NULL;

    if (!$creative_id || empty($name) || !$duration || !isset($bid_amount) || !in_array($status, ['active', 'paused'])) {
        redirect_with_message('error', 'Invalid data provided for update.', "video-creatives-edit.php?id=$creative_id");
    }
    
    $stmt = $conn->prepare("UPDATE video_creatives SET name = ?, bid_model = ?, bid_amount = ?, landing_url = ?, duration = ?, status = ?, impression_tracker = ? WHERE id = ?");
    $stmt->bind_param("ssdsissi", $name, $bid_model, $bid_amount, $landing_url, $duration, $status, $impression_tracker, $creative_id);

    if ($stmt->execute()) {
        redirect_with_message('success', 'Video creative updated successfully.', $redirect_url);
    } else {
        redirect_with_message('error', 'Failed to update video creative: ' . $stmt->error, $redirect_url);
    }
    $stmt->close();
}

// --- AKSI DELETE SATU CREATIVE ---
if (isset($_POST['delete_video_creative'])) {
    $creative_id = filter_input(INPUT_POST, 'creative_id', FILTER_VALIDATE_INT);
    if (!$creative_id) { redirect_with_message('error', 'Invalid creative ID.', $redirect_url); }
    
    $stmt_get = $conn->prepare("SELECT vast_type, video_url FROM video_creatives WHERE id = ?");
    $stmt_get->bind_param("i", $creative_id);
    $stmt_get->execute();
    $creative = $stmt_get->get_result()->fetch_assoc();
    if ($creative && $creative['vast_type'] === 'upload' && !empty($creative['video_url']) && file_exists(__DIR__ . '/' . $creative['video_url'])) {
        unlink(__DIR__ . '/' . $creative['video_url']);
    }
    $stmt_get->close();

    $stmt_delete = $conn->prepare("DELETE FROM video_creatives WHERE id = ?");
    $stmt_delete->bind_param("i", $creative_id);
    if ($stmt_delete->execute()) {
        redirect_with_message('success', 'Video creative has been deleted.', $redirect_url);
    } else {
        redirect_with_message('error', 'Failed to delete video creative.', $redirect_url);
    }
    $stmt_delete->close();
}

// --- AKSI TAMBAH CREATIVE BARU ---
if (isset($_POST['add_video_creative'])) {
    $campaign_id = filter_input(INPUT_POST, 'campaign_id', FILTER_VALIDATE_INT);
    $name = trim($_POST['name']);
    $bid_model = $_POST['bid_model'];
    $bid_amount = filter_input(INPUT_POST, 'bid_amount', FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $duration = filter_input(INPUT_POST, 'duration', FILTER_VALIDATE_INT);
    $vast_type = $_POST['vast_type'];
    $impression_tracker = filter_input(INPUT_POST, 'impression_tracker', FILTER_VALIDATE_URL) ?: NULL;
    $video_url = '';
    $landing_url = NULL;

    if ($vast_type !== 'third_party') {
        $landing_url = filter_input(INPUT_POST, 'landing_url', FILTER_VALIDATE_URL);
        if (!$landing_url) { redirect_with_message('error', "Landing Page URL is required for this source type.", $redirect_url); }
    }

    if (!$campaign_id || empty($name) || !$duration || !isset($bid_amount)) { 
        redirect_with_message('error', "Please fill all required fields correctly, including bid.", $redirect_url); 
    }

    try {
        switch ($vast_type) {
            case 'third_party':
                $video_url = filter_input(INPUT_POST, 'vast_url', FILTER_VALIDATE_URL);
                if (!$video_url) throw new Exception("A valid VAST Tag URL is required.");
                break;
            case 'hotlink':
                $video_url = filter_input(INPUT_POST, 'video_url_hotlink', FILTER_VALIDATE_URL);
                if (!$video_url) throw new Exception("A valid video hotlink URL is required.");
                break;
            case 'upload':
                if (!isset($_FILES['video_file_upload']) || $_FILES['video_file_upload']['error'] != 0) { throw new Exception("File upload error or no file selected."); }
                $upload_dir = __DIR__ . '/uploads/videos/';
                if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }
                $file_extension = pathinfo($_FILES['video_file_upload']['name'], PATHINFO_EXTENSION);
                $file_name = "vid_" . $campaign_id . '_' . time() . '.' . $file_extension;
                $target_file = $upload_dir . $file_name;
                if (!move_uploaded_file($_FILES['video_file_upload']['tmp_name'], $target_file)) { throw new Exception("Failed to move uploaded file."); }
                $video_url = 'uploads/videos/' . $file_name;
                break;
            default:
                throw new Exception("Invalid VAST type specified.");
        }

        $stmt = $conn->prepare("INSERT INTO video_creatives (campaign_id, name, bid_model, bid_amount, vast_type, video_url, landing_url, duration, impression_tracker) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssdssis", $campaign_id, $name, $bid_model, $bid_amount, $vast_type, $video_url, $landing_url, $duration, $impression_tracker);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Video creative "' . htmlspecialchars($name) . '" was created successfully.';
        } else {
            throw new Exception($stmt->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
    }
}

// Fallback redirect
header('Location: ' . $redirect_url);
exit();
?>