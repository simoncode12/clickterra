<?php
// File: /admin/supply-partners-action.php (FINAL REVISION - Handles reactivation correctly)

require_once __DIR__ . '/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: supply-partners.php');
    exit();
}

function redirect_with_message($type, $message) {
    $_SESSION[$type . '_message'] = $message;
    header('Location: supply-partners.php');
    exit();
}

// --- FUNGSI UNTUK MEMBUAT DEFAULT ZONE ---
// Kita buat fungsi terpisah agar bisa dipanggil dari dua tempat
function create_default_site_and_zone($conn, $user_id) {
    // Ambil username untuk nama situs default
    $stmt_user = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $user = $stmt_user->get_result()->fetch_assoc();
    if (!$user) { throw new Exception('Cannot find user to create default site.'); }
    $partner_name = $user['username'];
    $stmt_user->close();

    // Buat Situs Default
    $default_category_id = 1; 
    $default_site_url = 'https://rtb-partner.com/' . strtolower($partner_name);
    $stmt_site = $conn->prepare("INSERT INTO sites (user_id, category_id, url, status) VALUES (?, ?, ?, 'approved')");
    $stmt_site->bind_param("iis", $user_id, $default_category_id, $default_site_url);
    if (!$stmt_site->execute()) { throw new Exception('Failed to create default site for the partner.'); }
    $new_site_id = $conn->insert_id;
    $stmt_site->close();

    // Buat Zona Default
    $default_zone_name = 'Default RTB Zone (All Sizes)';
    $default_zone_size = 'all';
    $stmt_zone = $conn->prepare("INSERT INTO zones (site_id, name, size) VALUES (?, ?, ?)");
    $stmt_zone->bind_param("iss", $new_site_id, $default_zone_name, $default_zone_size);
    if (!$stmt_zone->execute()) { throw new Exception('Failed to create default zone for the partner.'); }
    
    return $conn->insert_id; // Kembalikan ID zona baru
}


// Aksi: Aktivasi Partner BARU
if (isset($_POST['activate_supply_partner'])) {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    if (!$user_id) { redirect_with_message('error', 'Invalid User ID.'); }
    
    $conn->begin_transaction();
    try {
        $partner_name_stmt = $conn->prepare("SELECT username FROM users WHERE id=?");
        $partner_name_stmt->bind_param('i', $user_id);
        $partner_name_stmt->execute();
        $partner_name = $partner_name_stmt->get_result()->fetch_assoc()['username'];
        
        $supply_key = bin2hex(random_bytes(16));
        $stmt_source = $conn->prepare("INSERT INTO rtb_supply_sources (user_id, name, supply_key, status) VALUES (?, ?, ?, 'active')");
        $stmt_source->bind_param("iss", $user_id, $partner_name, $supply_key);
        if (!$stmt_source->execute()) { throw new Exception('Failed to activate partner. Might already be active.'); }
        $new_source_id = $conn->insert_id;
        $stmt_source->close();

        // Panggil fungsi untuk membuat zona default
        $new_zone_id = create_default_site_and_zone($conn, $user_id);
        
        $stmt_update_source = $conn->prepare("UPDATE rtb_supply_sources SET default_zone_id = ? WHERE id = ?");
        $stmt_update_source->bind_param("ii", $new_zone_id, $new_source_id);
        $stmt_update_source->execute();
        $stmt_update_source->close();

        $conn->commit();
        redirect_with_message('success', 'Partner activated. Default site and zone (ID: ' . $new_zone_id . ') created and linked automatically.');

    } catch (Exception $e) {
        $conn->rollback();
        redirect_with_message('error', $e->getMessage());
    }
}


// Aksi: Mengubah Status Partner (PAUSE / RESUME)
if (isset($_POST['update_supply_status'])) {
    $source_id = filter_input(INPUT_POST, 'source_id', FILTER_VALIDATE_INT);
    $new_status = $_POST['new_status'];
    if (!$source_id || !in_array($new_status, ['active', 'paused'])) {
        redirect_with_message('error', 'Invalid data.');
    }

    $conn->begin_transaction();
    try {
        // === LOGIKA BARU SAAT RE-AKTIVASI ===
        if ($new_status === 'active') {
            // Cek dulu apakah partner ini sudah punya zona default
            $stmt_check = $conn->prepare("SELECT user_id, default_zone_id FROM rtb_supply_sources WHERE id = ?");
            $stmt_check->bind_param("i", $source_id);
            $stmt_check->execute();
            $source_data = $stmt_check->get_result()->fetch_assoc();
            $stmt_check->close();

            // Jika belum punya (kasus partner lama), buatkan sekarang
            if (empty($source_data['default_zone_id'])) {
                $new_zone_id = create_default_site_and_zone($conn, $source_data['user_id']);
                
                // Link zona baru ke supply source ini
                $stmt_link = $conn->prepare("UPDATE rtb_supply_sources SET default_zone_id = ? WHERE id = ?");
                $stmt_link->bind_param("ii", $new_zone_id, $source_id);
                $stmt_link->execute();
                $stmt_link->close();
            }
        }

        // Update status partner (selalu dijalankan)
        $stmt = $conn->prepare("UPDATE rtb_supply_sources SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $source_id);
        if (!$stmt->execute()) { throw new Exception('Failed to update partner status.'); }
        $stmt->close();
        
        $conn->commit();
        redirect_with_message('success', 'Supply Partner status has been updated.');

    } catch (Exception $e) {
        $conn->rollback();
        redirect_with_message('error', 'Failed to update status: ' . $e->getMessage());
    }
}
?>