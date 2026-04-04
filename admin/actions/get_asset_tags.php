<?php
session_start();
include '../../includes/db_connect.php'; // Adjust path if needed

// Security: Only Admins should see this
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    exit("Unauthorized");
}

$asset_id = $_GET['asset_id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM asset_tags WHERE asset_id = ?");
$stmt->execute([$asset_id]);
$tags = $stmt->fetchAll();

if (empty($tags)) {
    echo '<div class="p-3 text-center text-muted">No individual tags registered for this asset.</div>';
} else {
    echo '<table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Asset Tag / Serial</th>
                    <th>Current Status</th>
                </tr>
            </thead>
            <tbody>';
    foreach ($tags as $t) {
        $badge = ($t['status'] == 'Available') ? 'bg-success' : 'bg-danger';
        echo "<tr>
                <td class='fw-bold'>{$t['unique_tag']}</td>
                <td><span class='badge rounded-pill {$badge}'>{$t['status']}</span></td>
              </tr>";
    }
    echo '</tbody></table>';
}