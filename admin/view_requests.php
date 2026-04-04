<?php
session_start();
include '../includes/db_connect.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch requests with User and Asset details - r.* includes the assigned_tag column
$query = "SELECT r.*, u.full_name, a.asset_name, a.category 
          FROM borrowing_requests r
          JOIN users u ON r.user_id = u.user_id
          JOIN assets a ON r.asset_id = a.asset_id
          ORDER BY r.request_date DESC";
$stmt = $pdo->query($query);
$requests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Borrowing Requests - Nuqtah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root { --sidebar-width: 260px; --teal-primary: #00796B; }
        body { background-color: #f8f9fa; }
        
        .sidebar { 
            width: var(--sidebar-width); 
            height: 100vh; 
            position: fixed; 
            top: 0; 
            left: 0; 
            background-color: white; 
            border-right: 1px solid #eee; 
            z-index: 1000; 
        }

        .bg-teal { background-color: #00796B !important; }
        .modal-header .modal-title { color: #ffffff !important; }
        .main-content { margin-left: var(--sidebar-width); padding: 30px; }
        .status-badge { font-size: 0.85rem; padding: 6px 12px; }
        
        .nav-link { color: #555; padding: 10px 20px; margin: 2px 10px; border-radius: 8px; text-decoration: none; display: block; }
        .nav-link.active { background-color: var(--teal-primary) !important; color: white !important; }
        .nav-link:hover:not(.active) { background-color: #f1f1f1; }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="mb-4">
        <h2 class="fw-bold">Borrowing Requests</h2>
        <p class="text-muted">Review and manage equipment loan applications for ITQSHHB.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Requester</th>
                        <th>Asset Item</th>
                        <th>Asset Tag</th> <th>Quantity</th> 
                        <th>Request Date</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $row): ?>
                        <?php 
                            $status = $row['status'];
                            $badge_class = "bg-secondary";
                            if ($status == 'Pending') $badge_class = "bg-warning text-dark";
                            elseif ($status == 'Approved' || $status == 'On Loan') $badge_class = "bg-success";
                            elseif ($status == 'Rejected') $badge_class = "bg-danger";
                            elseif ($status == 'Returned') $badge_class = "bg-info text-dark";
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                <small class="text-muted">User ID: #<?php echo $row['user_id']; ?></small>
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($row['asset_name']); ?></div>
                                <span class="badge bg-light text-dark border"><?php echo $row['category']; ?></span>
                            </td>
                            
                            <td>
                                <?php if (!empty($row['assigned_tag'])): ?>
                                    <span class="badge bg-teal text-white shadow-sm" style="font-family: monospace;">
                                        <i class="bi bi-tag-fill me-1"></i><?php echo htmlspecialchars($row['assigned_tag']); ?>
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
                            <td><?php echo date('d M Y', strtotime($row['request_date'])); ?></td>
                            <td>
                                <span class="badge <?php echo $badge_class; ?> rounded-pill status-badge">
                                    <?php echo $status; ?>
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
                                    <button class="btn btn-sm btn-dark rounded-pill px-3" onclick="processReturn(<?php echo $row['request_id']; ?>)">
                                        <i class="bi bi-arrow-left-right me-1"></i>Mark Returned
                                    </button>
                                    
                                <?php else: ?>
                                    <span class="text-muted small italic"><?php echo $status; ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="check1" required>
                        <label class="form-check-label" for="check1">Item is physically intact</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="check2" required>
                        <label class="form-check-label" for="check2">All peripherals included</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="check3" required>
                        <label class="form-check-label" for="check3">Device powers on correctly</label>
                    </div>

                    <div class="mt-4 p-3 bg-light rounded-3 border">
                        <label class="form-label small fw-bold text-dark">Assign Specific Asset Tag</label>
                        <select class="form-select border-success shadow-sm" name="assigned_tag" id="tag_dropdown" required>
                            <option value="" disabled selected>Loading available items...</option>
                        </select>
                        <div class="form-text text-muted" style="font-size: 0.75rem;">
                            <i class="bi bi-info-circle me-1"></i>Select the specific serial tag for this handover.
                        </div>
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
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" required>
                        <label class="form-check-label">Item returned in good condition</label>
                    </div>
                    <textarea class="form-control" name="return_note" rows="2" placeholder="Return notes..."></textarea>
                </div>
                <div class="modal-footer border-0">
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

    var myModal = new bootstrap.Modal(document.getElementById('handoverModal'));
    myModal.show();

    fetch(`actions/get_available_tags.php?asset_id=${assetId}`)
        .then(response => response.text())
        .then(data => { dropdown.innerHTML = data; })
        .catch(err => { dropdown.innerHTML = '<option disabled>Error loading tags</option>'; });
}

function processReturn(id) {
    document.getElementById('return_request_id').value = id;
    var returnModal = new bootstrap.Modal(document.getElementById('returnModal'));
    returnModal.show();
}
</script>

</body>
</html>