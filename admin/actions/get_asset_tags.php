<?php
session_start();
include '../../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized");
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
$asset_id = $_GET['asset_id'] ?? 0;
$showActions = $isAdmin && (!isset($_GET['show_actions']) || $_GET['show_actions'] !== '0');

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
                    <th>Current Status</th>' . ($showActions ? '<th class="text-end">Action</th>' : '') . '
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
                <td class='fw-bold text-dark'>" . htmlspecialchars($t['unique_tag']) . "</td>
                <td><span class='badge rounded-pill {$badge}'>" . htmlspecialchars($t['status']) . "</span></td>";

        if ($showActions) {
            echo "<td class='text-end'>";
            if ($t['status'] == 'Maintenance' || $t['status'] == 'Damaged') {
                echo "<button class='btn btn-sm btn-outline-success border-0 me-2' 
                              onclick='markAsAvailable({$t['tag_id']}, {$t['asset_id']}, \"" . htmlspecialchars($t['unique_tag']) . "\")'
                              title='Mark as Available'>
                        <i class='bi bi-check-circle-fill'></i>
                      </button>";
            }

            echo "<button class='btn btn-sm btn-outline-danger border-0' 
                            onclick='deleteTag({$t['tag_id']}, {$t['asset_id']})'
                            title='Delete Serial'>
                        <i class='bi bi-trash3'></i>
                    </button>
                </td>";
        }

        echo "</tr>";
    }
    echo '</tbody></table>';
}