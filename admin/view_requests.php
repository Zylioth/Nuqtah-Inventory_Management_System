<?php
session_start();
include '../includes/db_connect.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

// 1. UPDATED QUERY: Added r.issued_date to the SELECT statement
$query = "SELECT r.*, u.full_name, a.asset_name, a.category, t.unique_tag as assigned_tag_name 
          FROM borrowing_requests r
          JOIN users u ON r.user_id = u.user_id
          JOIN assets a ON r.asset_id = a.asset_id
          LEFT JOIN asset_tags t ON r.tag_id = t.tag_id
          ORDER BY r.request_date DESC";
$stmt = $pdo->query($query);
$requests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowing Requests - Nuqtah</title>
    <link rel="icon" type="image/png" href="/Nuqtah_IT/assets/img/Nuqtah_logo_small.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root { --teal-primary: #00796B; --teal-dark: #004D40; }
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
        
        /* Full Page Layout */
        .main-content { padding: 40px 0; }
        .requests-container { max-width: 1400px; margin: 0 auto; }

        .bg-teal { background-color: var(--teal-primary) !important; }
        .bg-purple { background-color: #6f42c1 !important; }
        .modal-header .modal-title { color: #ffffff !important; }
        
        .status-badge { font-size: 0.85rem; padding: 6px 12px; }
        .card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .table-hover tbody tr:hover { background-color: #f0f7f6; transition: 0.2s; }

        /* Custom styling for the timeline schedule labels */
        .schedule-label { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; display: block; color: #6c757d; margin-bottom: -2px; }
        .schedule-val { font-size: 13px; display: block; margin-bottom: 8px; }
        .schedule-val:last-child { margin-bottom: 0; }

        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }

        .animate-pulse {
            /* This connects the class to the keyframes above */
            animation: pulse-red 2s infinite;
        }

    </style>
</head>
<body>

<div class="main-content">
    <div class="container-fluid requests-container">

        <div class="mb-4">
            <div class="d-flex align-items-center mb-3">
                <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 me-3">
                    <i class="bi bi-arrow-left"></i> Dashboard
                </a>
                <h2 class="fw-bold mb-0">Borrowing Requests</h2>
            </div>
            <div class="ms-md-5 ps-md-2">
                <p class="text-muted mb-0">Review and manage equipment loan applications for ITQSHHB.</p>
            </div>
        </div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-dismissible fade show border-0 shadow-sm rounded-3 ms-md-5 mb-4 
        <?php 
            if ($_GET['msg'] == 'issued' || $_GET['msg'] == 'approved') echo 'alert-success';
            elseif ($_GET['msg'] == 'returned') echo 'alert-info';
            elseif ($_GET['msg'] == 'rejected') echo 'alert-warning';
            elseif ($_GET['msg'] == 'error') echo 'alert-danger';
            else echo 'alert-primary';
        ?>" role="alert">
        
        <i class="bi <?php 
            if ($_GET['msg'] == 'issued' || $_GET['msg'] == 'approved') echo 'bi-check-circle-fill';
            elseif ($_GET['msg'] == 'returned') echo 'bi-arrow-left-right';
            elseif ($_GET['msg'] == 'rejected') echo 'bi-x-circle-fill';
            else echo 'bi-exclamation-triangle-fill';
        ?> me-2"></i>

        <strong>
            <?php 
                if ($_GET['msg'] == 'approved') echo 'Request has been Approved!';
                elseif ($_GET['msg'] == 'issued') echo 'Item Issued Successfully!';
                elseif ($_GET['msg'] == 'returned') echo 'Item Returned & Restocked!';
                elseif ($_GET['msg'] == 'rejected') echo 'Request has been Rejected.';
                elseif ($_GET['msg'] == 'error') echo 'Something went wrong. Please try again.';
                else echo 'Action completed.';
            ?>
        </strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-5">
                <div class="input-group shadow-sm ms-md-5">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" id="requestSearch" class="form-control border-start-0 ps-0" 
                        placeholder="Search by requester, asset, or status..." onkeyup="filterRequests()">
                </div>
            </div>
        </div>

        <div class="card rounded-4 overflow-hidden ms-md-5">
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3">Requester</th>
                            <th>Asset Item</th>
                            <th>Asset Tag</th> 
                            <th>Qty</th> 
                            <th>Timeline Schedule</th> 
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
<tbody>
                        <?php foreach ($requests as $row): ?>
                            <?php 
                                // 1. OVERDUE LOGIC
                                $today = date('Y-m-d');
                                $isOverdue = ($row['status'] == 'On Loan' && $today > $row['return_date']);

                                $status = $row['status'];
                                $badge_class = "bg-secondary text-white";
                                
                                // 2. UPDATED BADGE LOGIC (Adds Overdue Support)
                                if ($isOverdue) {
                                    $badge_class = "bg-danger text-white animate-pulse";
                                    $display_status = "OVERDUE";
                                } else {
                                    $display_status = $status;
                                    if ($status == 'Pending') $badge_class = "bg-warning text-dark";
                                    elseif ($status == 'Approved') $badge_class = "bg-primary text-white";
                                    elseif ($status == 'On Loan') $badge_class = "bg-success text-white";
                                    elseif ($status == 'Issued') $badge_class = "bg-purple text-white"; 
                                    elseif ($status == 'Returned') $badge_class = "bg-info text-dark";
                                    elseif ($status == 'Rejected') $badge_class = "bg-danger text-white";
                                }
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                    <small class="text-muted">ID: #<?php echo $row['user_id']; ?></small>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($row['asset_name']); ?></div>
                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['category']); ?></span>
                                </td>
                                
                                <td>
                                    <?php if (!empty($row['assigned_tag_name'])): ?>
                                        <span class="badge bg-teal text-white shadow-sm" style="font-family: monospace;">
                                            <i class="bi bi-tag-fill me-1"></i><?php echo htmlspecialchars($row['assigned_tag_name']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">Not Assigned</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="badge rounded-pill" style="background-color: rgba(0, 121, 107, 0.1); color: #004D40;">
                                        <?php echo htmlspecialchars($row['quantity']); ?>
                                    </span>
                                </td>

                                <td style="min-width: 200px;">
                                    <div class="d-flex flex-column py-1">
                                        <span class="schedule-label">Requested</span>
                                        <span class="schedule-val text-dark"><?php echo date('d M Y', strtotime($row['request_date'])); ?></span>
                                        
                                        <span class="schedule-label">Issued On</span>
                                        <span class="schedule-val">
                                            <?php if (!empty($row['issued_date'])): ?>
                                                <span class="text-primary fw-semibold"><i class="bi bi-box-arrow-right me-1"></i><?php echo date('d M Y, h:i A', strtotime($row['issued_date'])); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted italic small">Waiting for handover</span>
                                            <?php endif; ?>
                                        </span>

                                        <span class="schedule-label">Return Due</span>
                                        <span class="schedule-val <?php echo $isOverdue ? 'text-danger fw-bold' : 'text-muted'; ?>">
                                            <?php echo !empty($row['return_date']) ? date('d M Y', strtotime($row['return_date'])) : 'N/A'; ?>
                                            <?php if ($isOverdue): ?>
                                                <i class="bi bi-exclamation-triangle-fill ms-1"></i>
                                            <?php endif; ?>
                                        </span>

                                    <?php if (!empty($row['actual_return_date'])): ?>
                                        <?php 
                                            // 1. Prepare clean date strings for comparison (YYYY-MM-DD)
                                            $deadline_date = date('Y-m-d', strtotime($row['return_date']));
                                            $actual_date = date('Y-m-d', strtotime($row['actual_return_date']));

                                            // 2. Logic: Only RED if strictly AFTER the deadline
                                            $isLate = ($actual_date > $deadline_date);
                                            $statusColor = $isLate ? 'text-danger' : 'text-success';
                                        ?>
                                        
                                        <span class="schedule-label <?php echo $statusColor; ?>">Actually Returned</span>
                                        <span class="schedule-val <?php echo $statusColor; ?> fw-bold">
                                            <i class="bi <?php echo $isLate ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill'; ?> me-1"></i>
                                            <?php echo date('d M Y', strtotime($row['actual_return_date'])); ?>
                                            <?php if ($isLate): ?>
                                                <small class="d-block" style="font-size: 0.65rem;">(LATE RETURN)</small>
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <span class="badge <?php echo $badge_class; ?> rounded-pill status-badge">
                                        <?php echo htmlspecialchars($display_status); ?>
                                    </span>
                                </td>

                                <td class="text-end pe-4">
                                    <?php if ($status == 'Pending'): ?>
                                        <a href="actions/process_request.php?id=<?php echo $row['request_id']; ?>&action=approve" class="btn btn-sm btn-success rounded-pill px-3">Approve</a>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="rejectRequest(<?php echo $row['request_id']; ?>)">Reject</button>
                                    
                                    <?php elseif ($status == 'Approved'): ?>
                                        <button class="btn btn-sm btn-primary rounded-pill px-3" 
                                                onclick="openHandoverModal(<?php echo $row['request_id']; ?>, <?php echo $row['asset_id']; ?>)">
                                            <i class="bi bi-hand-index-thumb me-1"></i>Issue Item
                                        </button>

                                    <?php elseif ($status == 'On Loan'): ?>
                                        <button class="btn <?php echo $isOverdue ? 'btn-danger' : 'btn-dark'; ?> btn-sm rounded-pill px-3 shadow-sm" onclick="processReturn(<?php echo $row['request_id']; ?>)">
                                            <i class="bi bi-arrow-left-right me-1"></i>Mark Returned
                                        </button>
                                        
                                    <?php elseif ($status == 'Issued'): ?>
                                        <span class="text-muted small"><i class="bi bi-check2-all me-1"></i>Finalized</span>
                                    
                                    <?php else: ?>
                                        <span class="text-muted small italic"><?php echo htmlspecialchars($status); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="handoverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-teal rounded-top-4">
                <h5 class="modal-title fw-bold text-white">
                    <i class="bi bi-clipboard-check me-2"></i>Pre-Loan Inspection
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="actions/process_handover.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="request_id" id="modal_request_id">
                    <p class="text-muted small mb-4">Verify asset condition before handing over to the student.</p>
                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="check1" required><label class="form-check-label" for="check1">Item is physically intact</label></div>
                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="check2" required><label class="form-check-label" for="check2">All peripherals included</label></div>
                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="check3" required><label class="form-check-label" for="check3">Device powers on correctly</label></div>

                    <div class="mt-4 p-3 bg-light rounded-3 border">
                        <label class="form-label small fw-bold text-dark">Assign Specific Asset Tag</label>
                        <select class="form-select border-success shadow-sm" name="assigned_tag" id="tag_dropdown" required>
                            <option value="" disabled selected>Loading available items...</option>
                        </select>
                    </div>

                    <div class="mt-4">
                        <label class="form-label small fw-bold">Admin Handover Notes (Optional)</label>
                        <textarea class="form-control rounded-3" name="handover_note" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">Confirm & Issue Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark rounded-top-4">
                <h5 class="modal-title fw-bold text-white">Post-Loan Inspection</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="actions/process_return.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="request_id" id="return_request_id">
                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <label class="form-label small fw-bold text-dark">Asset Return Status</label>
                        <select class="form-select border-dark shadow-sm" name="return_status" required>
                            <option value="Available" selected>✅ Good Condition (Back to Stock)</option>
                            <option value="Maintenance">🛠️ Under Maintenance (Needs Repair)</option>
                            <option value="Damaged">❌ Damaged / Broken (Remove from Stock)</option>
                        </select>
                    </div>
                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="confirmInspect" required><label class="form-check-label small" for="confirmInspect">I have verified the physical condition.</label></div>
                    <div class="mt-3">
                        <label class="form-label small fw-bold">Return Notes / Observations</label>
                        <textarea class="form-control rounded-3" name="return_note" rows="2" placeholder="e.g. Scratches on lid..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4">Confirm Return</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function rejectRequest(id) {
    const reason = prompt("Please enter the reason for rejection:");
    if (reason != null) {
        window.location.href = `actions/process_request.php?id=${id}&action=reject&note=${encodeURIComponent(reason)}`;
    }
}

function openHandoverModal(id, assetId) {
    document.getElementById('modal_request_id').value = id;
    const dropdown = document.getElementById('tag_dropdown');
    dropdown.innerHTML = '<option disabled>Fetching available tags...</option>';
    new bootstrap.Modal(document.getElementById('handoverModal')).show();

    fetch(`actions/get_available_tags.php?asset_id=${assetId}`)
        .then(response => response.text())
        .then(data => { dropdown.innerHTML = data; })
        .catch(err => { dropdown.innerHTML = '<option disabled>Error loading tags</option>'; });
}

function processReturn(id) {
    document.getElementById('return_request_id').value = id;
    new bootstrap.Modal(document.getElementById('returnModal')).show();
}

function filterRequests() {
    const filter = document.getElementById("requestSearch").value.toLowerCase();
    const rows = document.querySelectorAll("table tbody tr");
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? "" : "none";
    });
}
</script>

</body>
</html>