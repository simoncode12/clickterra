<?php
// File: /admin/zone-action.php (NEW)

require_once __DIR__ . '/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: zone.php');
    exit();
}

function redirect_with_message($type, $message) {
    $_SESSION[$type . '_message'] = $message;
    header('Location: zone.php');
    exit();
}

// Aksi: Tambah Zona Baru
if (isset($_POST['add_zone'])) {
    $site_id = filter_input(INPUT_POST, 'site_id', FILTER_VALIDATE_INT);
    $name = trim($_POST['name']);
    $size = trim($_POST['size']);

    if (!$site_id || empty($name) || empty($size)) {
        redirect_with_message('error', 'Please fill all fields correctly.');
    }

    $stmt = $conn->prepare("INSERT INTO zones (site_id, name, size) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $site_id, $name, $size);

    if ($stmt->execute()) {
        redirect_with_message('success', 'Zone "' . htmlspecialchars($name) . '" added successfully.');
    } else {
        redirect_with_message('error', 'Failed to add zone: ' . $stmt->error);
    }
    $stmt->close();
}

// Aksi: Update Zona
if (isset($_POST['update_zone'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $name = trim($_POST['name']);
    $size = trim($_POST['size']);
    
    if (!$id || empty($name) || empty($size)) {
        redirect_with_message('error', 'Invalid data provided for update.');
    }

    $stmt = $conn->prepare("UPDATE zones SET name = ?, size = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $size, $id);

    if ($stmt->execute()) {
        redirect_with_message('success', 'Zone "' . htmlspecialchars($name) . '" updated successfully.');
    } else {
        redirect_with_message('error', 'Failed to update zone: ' . $stmt->error);
    }
    $stmt->close();
}

// Aksi: Hapus Zona
if (isset($_POST['delete_zone'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (!$id) {
        redirect_with_message('error', 'Invalid zone ID.');
    }

    $stmt = $conn->prepare("DELETE FROM zones WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        redirect_with_message('success', 'Zone deleted successfully.');
    } else {
        redirect_with_message('error', 'Failed to delete zone: ' . $stmt->error);
    }
    $stmt->close();
}

header('Location: zone.php');
exit();
?>