<?php
// File: /admin/ssp-action.php (UPDATED to generate unique keys)

require_once __DIR__ . '/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ssp.php');
    exit();
}

function redirect_with_message($type, $message) {
    $_SESSION[$type . '_message'] = $message;
    header('Location: ssp.php');
    exit();
}

// Add Partner
if (isset($_POST['add_partner'])) {
    $name = trim($_POST['name']);
    $endpoint_url = filter_input(INPUT_POST, 'endpoint_url', FILTER_VALIDATE_URL);
    
    // Generate a unique secret key for the new partner
    $partner_key = bin2hex(random_bytes(16)); // Creates a 32-character hex key

    if (empty($name) || !$endpoint_url) {
        redirect_with_message('error', 'Please provide a valid name and endpoint URL.');
    }

    $stmt = $conn->prepare("INSERT INTO ssp_partners (name, endpoint_url, partner_key) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $endpoint_url, $partner_key);
    if ($stmt->execute()) {
        redirect_with_message('success', 'SSP Partner added successfully.');
    } else {
        redirect_with_message('error', 'Failed to add partner: ' . $stmt->error);
    }
}

// Update Partner (Endpoint URL saja yang bisa diubah, key tetap)
if (isset($_POST['update_partner'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $name = trim($_POST['name']);
    $endpoint_url = filter_input(INPUT_POST, 'endpoint_url', FILTER_VALIDATE_URL);

    if (!$id || empty($name) || !$endpoint_url) {
        redirect_with_message('error', 'Invalid data provided for update.');
    }

    $stmt = $conn->prepare("UPDATE ssp_partners SET name = ?, endpoint_url = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $endpoint_url, $id);
     if ($stmt->execute()) {
        redirect_with_message('success', 'SSP Partner updated successfully.');
    } else {
        redirect_with_message('error', 'Failed to update partner: ' . $stmt->error);
    }
}

// Delete Partner
if (isset($_POST['delete_partner'])) {
    // ... (Logika hapus tetap sama seperti sebelumnya)
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$id) { redirect_with_message('error', 'Invalid ID.'); }
    $stmt = $conn->prepare("DELETE FROM ssp_partners WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) { redirect_with_message('success', 'SSP Partner deleted successfully.'); } 
    else { redirect_with_message('error', 'Failed to delete partner.'); }
}
?>