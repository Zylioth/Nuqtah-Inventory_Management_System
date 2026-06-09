<?php
session_start();
include 'includes/db_connect.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Mark notifications as read
$clearNotif = $pdo->prepare("UPDATE borrowing_requests SET is_read = 1 WHERE user_id = ? AND is_read = 0");
$clearNotif->execute([$user_id]);

// Fetch current user requests with joined asset and tag information
$query = "SELECT r.*, a.asset_name, a.category, t.unique_tag 
          FROM borrowing_requests r
          JOIN assets a ON r.asset_id = a.asset_id
          LEFT JOIN asset_tags t ON r.tag_id = t.tag_id
          WHERE r.user_id = ?
          ORDER BY r.request_date DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$my_requests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Requests - Nuqtah</title>
    <link rel="icon" type="image/png" href="/Nuqtah_IT/assets/img/Nuqtah_logo_small.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { 
            --teal-primary: #00796B; 
            --teal-dark: #004D40; 
        }
        body { 
            background-color: #f8f9fa; 
            font-family: 'Inter', sans-serif;
        }
        .main-content {
            padding: 40px 15px;
            max-width: 1300px;
            margin: 0 auto;
        }
        
        /* Modernized visual tokens */
        .card { 
            border: none; 
            border-radius: 16px; 
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.05); 
        }
        .table-hover tbody tr:hover { 
            background-color: #f0f7f6; 
            transition: 0.2s; 
        }

        /* Soft pastel badge theme */
        .status-badge { 
            font-size: 0.8rem; 
            padding: 6px 14px; 
            font-weight: 600;
        }
        .badge-pending { background-color: #FFF9C4; color: #F57F17; }
        .badge-approved { background-color: #E3F2FD; color: #0D47A1; }
        .badge-onloan { background-color: #E8F5E9; color: #1B5E20; }
        .badge-issued { background-color: #EDE7F6; color: #4A148C; }
        .badge-returned { background-color: #E0F7FA; color: #006064; }
        .badge-rejected { background-color: #FFEBEE; color: #B71C1C; }

        .tag-badge { 
            background-color: rgba(0, 121, 107, 0.08); 
            color: var(--teal-primary); 
            font-family: monospace; 
            font-size: 0.8rem; 
            border: 1px solid rgba(0, 121, 107, 0.12);
            padding: 4px 10px;
        }

        .btn-teal-outline {
            color: var(--teal-primary);
            border: 1.5px solid var(--teal-primary);
            background: transparent;
            font-weight: 600;
        }
        .btn-teal-outline:hover {
            background-color: var(--teal-primary);
            color: #ffffff;
        }
    </style>
</head>
<body>

<div class="main-content">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">My Borrowing History</h2>
            <p class="text-muted mb-0">Track and manage your institutional equipment loans.</p>
        </div>
        <a href="inventory_list.php" class="btn btn-teal-outline rounded-pill px-4">
            <i class="bi bi-arrow-left me-2"></i>Back to Inventory
        </a>
    </div>

    <div class="card overflow-hidden">
        <?php if (count($my_requests) > 0): ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Asset & Tag</th>
                            <th style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Qty</th>
                            <th style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Date Requested</th>
                            <th style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Status</th>
                            <th class="text-end pe-4" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Admin Note / Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($my_requests as $row): ?>
                            <?php 
                                $status = $row['status'];
                                $badge_class = 'badge-pending';

                                if ($status == 'Pending') {
                                    $badge_class = 'badge-pending';
                                } elseif ($status == 'Approved') {
                                    $badge_class = 'badge-approved';
                                } elseif ($status == 'On Loan') {
                                    $badge_class = 'badge-onloan';
                                } elseif ($status == 'Issued') {
                                    $badge_class = 'badge-issued';
                                } elseif ($status == 'Returned') {
                                    $badge_class = 'badge-returned';
                                } elseif ($status == 'Rejected') {
                                    $badge_class = 'badge-rejected';
                                }
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['asset_name']); ?></div>
                                    <div class="mt-1 d-flex align-items-center gap-2">
                                        <small class="text-muted"><?php echo htmlspecialchars($row['category']); ?></small>
                                        <?php if (!empty($row['unique_tag'])): ?>
                                            <span class="badge tag-badge rounded-pill">
                                                <i class="bi bi-tag-fill me-1"></i><?php echo htmlspecialchars($row['unique_tag']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold" style="color: var(--teal-primary);"><?php echo (int)$row['quantity']; ?></span>
                                </td>
                                <td>
                                    <span class="text-dark"><?php echo date('d M Y', strtotime($row['request_date'])); ?></span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill status-badge <?php echo $badge_class; ?>">
                                        <?php echo htmlspecialchars($status); ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4 small">
                                    <div class="py-1">
                                        <?php 
                                            if ($status == 'Rejected' && !empty($row['admin_note'])) {
                                                echo '<span class="text-danger fw-bold"><i class="bi bi-x-circle-fill me-1"></i>Reason:</span> ' . htmlspecialchars($row['admin_note']);
                                            } 
                                            elseif ($status == 'On Loan' && !empty($row['condition_note'])) {
                                                echo '<span class="text-dark fw-bold"><i class="bi bi-info-circle-fill me-1"></i>Condition:</span> ' . htmlspecialchars($row['condition_note']);
                                            }
                                            elseif ($status == 'Issued') {
                                                echo '<span class="text-dark fw-bold"><i class="bi bi-clipboard-check-fill me-1"></i>Note:</span> Handed over for permanent use.';
                                                if(!empty($row['condition_note'])) {
                                                    echo '<div class="text-muted mt-1" style="font-size: 0.75rem;">' . htmlspecialchars($row['condition_note']) . '</div>';
                                                }
                                            }
                                            elseif ($status == 'Returned' && !empty($row['return_note'])) {
                                                echo '<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Return Note:</span> ' . htmlspecialchars($row['return_note']);
                                            }
                                            elseif ($status == 'Approved') {
                                                echo '<span class="text-primary fw-bold"><i class="bi bi-bell-fill me-1"></i>Ready:</span> Head to ICT Desk for collection.';
                                            }
                                            else {
                                                echo '<span class="text-muted italic">No details available</span>';
                                            }
                                        ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <!-- Dynamic, polished empty state fallback -->
            <div class="text-center py-5">
                <div class="mb-4">
                    <span class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle text-muted" style="width: 80px; height: 80px;">
                        <i class="bi bi-inbox fs-1"></i>
                    </span>
                </div>
                <h5 class="fw-bold text-dark">No Borrowing History Found</h5>
                <p class="text-muted small px-3">You have not submitted any equipment requests yet.</p>
                <a href="inventory_list.php" class="btn btn-sm btn-teal-outline rounded-pill px-4 mt-2">
                    Browse Asset Inventory
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>