<?php
session_start();
include '../../includes/db_connect.php';

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
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>';
    foreach ($tags as $t) {
        // Dynamic Badge Colors
        $badge = 'bg-secondary'; // Default
        if ($t['status'] == 'Available') $badge = 'bg-success';
        if ($t['status'] == 'On Loan' || $t['status'] == 'Issued') $badge = 'bg-primary';
        if ($t['status'] == 'Maintenance' || $t['status'] == 'Damaged') $badge = 'bg-danger';

        echo "<tr>
                <td class='fw-bold text-dark'>{$t['unique_tag']}</td>
                <td><span class='badge rounded-pill {$badge}'>{$t['status']}</span></td>
                <td class='text-end'>";
        
        // --- ADDED: REPAIR BUTTON ---
        // Only show if status is Maintenance or Damaged
        if ($t['status'] == 'Maintenance' || $t['status'] == 'Damaged') {
            echo "<button class='btn btn-sm btn-outline-success border-0 me-2' 
                          onclick='markAsAvailable({$t['tag_id']}, {$t['asset_id']}, \"{$t['unique_tag']}\")'
                          title='Mark as Available'>
                    <i class='bi bi-check-circle-fill'></i>
                  </button>";
        }

        echo "      <button class='btn btn-sm btn-outline-danger border-0' 
                            onclick='deleteTag({$t['tag_id']}, {$t['asset_id']})'
                            title='Delete Serial'>
                        <i class='bi bi-trash3'></i>
                    </button>
                </td>
            </tr>";
    }
    echo '</tbody></table>';
}