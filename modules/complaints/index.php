<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

// Generate a unique ticket number
function generateTicketNumber() {
    return 'TK-' . strtoupper(substr(uniqid(), -6));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $subject = $_POST['subject'];
        $desc = $_POST['description'];
        $priority = $_POST['priority'];
        $resident_id = !empty(trim($_POST['resident_id'])) ? trim($_POST['resident_id']) : null;
        
        $valid_resident = true;
        if ($resident_id !== null) {
            $checkStmt = $pdo->prepare("SELECT id FROM individuals WHERE id = ?");
            $checkStmt->execute([$resident_id]);
            if (!$checkStmt->fetchColumn()) {
                $valid_resident = false;
                $error = "Registration failed: The provided Resident ID does not exist in the database.";
            }
        }

        if ($valid_resident) {
            $ticket_no = generateTicketNumber();
            $stmt = $pdo->prepare("INSERT INTO complaints (ticket_number, resident_id, subject, description, priority) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$ticket_no, $resident_id, $subject, $desc, $priority]);
            $success = "Ticket $ticket_no generated successfully!";
            
            // Activity log
            log_activity($pdo, $_SESSION['user_id'] ?? null, "Created Complaint Ticket: $ticket_no");
        }
    } elseif ($_POST['action'] === 'update_status') {
        $id = $_POST['ticket_id'];
        $status = $_POST['status'];
        $stmt = $pdo->prepare("UPDATE complaints SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        $success = "Ticket status updated successfully!";
        
        $ticket_no = $pdo->query("SELECT ticket_number FROM complaints WHERE id = $id")->fetchColumn();
        log_activity($pdo, $_SESSION['user_id'] ?? null, "Updated Ticket $ticket_no to $status");
    }
}

// Fetch complaints
$stmt = $pdo->query("
    SELECT c.*, i.fname, i.lname, a.pho_no 
    FROM complaints c 
    LEFT JOIN individuals i ON c.resident_id = i.id 
    LEFT JOIN addresses a ON i.id = a.id
    ORDER BY c.created_at DESC
");
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getStatusBadge($status) {
    $map = [
        'open' => 'bg-danger',
        'in_progress' => 'bg-warning text-dark',
        'resolved' => 'bg-info',
        'closed' => 'bg-success'
    ];
    $display = ucwords(str_replace('_', ' ', $status));
    return "<span class='badge " . $map[$status] . "'>$display</span>";
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800 fw-bold"><i class="fas fa-ticket-alt text-danger me-2"></i><?php echo __('resident_service_requests'); ?></h2>
            <p class="text-muted mb-0"><?php echo __('manage_complaints_desc'); ?></p>
        </div>
        <button class="btn btn-primary shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addComplaintModal">
            <i class="fas fa-plus me-2"></i><?php echo __('new_ticket'); ?>
        </button>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4"><?php echo __('ticket_date'); ?></th>
                            <th><?php echo __('resident_details'); ?></th>
                            <th><?php echo __('subject_description'); ?></th>
                            <th><?php echo __('priority'); ?></th>
                            <th><?php echo __('status'); ?></th>
                            <th class="text-end pe-4"><?php echo __('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($complaints)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted"><?php echo __('no_tickets_found'); ?></td></tr>
                        <?php else: ?>
                            <?php foreach ($complaints as $c): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-primary"><?php echo $c['ticket_number']; ?></div>
                                        <small class="text-muted"><i class="far fa-clock me-1"></i><?php echo date('M d, Y h:ia', strtotime($c['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($c['fname']): ?>
                                            <div class="fw-bold"><i class="fas fa-user-circle me-1 text-secondary"></i><?php echo htmlspecialchars($c['fname'] . ' ' . $c['lname']); ?></div>
                                            <small class="text-muted"><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($c['pho_no']); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">Anonymous/External</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($c['subject']); ?></div>
                                        <small class="text-muted d-inline-block text-truncate" style="max-width: 200px;"><?php echo htmlspecialchars($c['description']); ?></small>
                                    </td>
                                    <td>
                                        <?php 
                                            $pcolor = 'secondary';
                                            if ($c['priority'] == 'high') $pcolor = 'warning';
                                            if ($c['priority'] == 'urgent') $pcolor = 'danger';
                                        ?>
                                        <span class="badge bg-<?php echo $pcolor; ?> rounded-pill"><i class="fas fa-flag me-1"></i><?php echo ucfirst($c['priority']); ?></span>
                                    </td>
                                    <td><?php echo getStatusBadge($c['status']); ?></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-light rounded-circle shadow-sm me-1" 
                                                data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $c['id']; ?>">
                                            <i class="fas fa-eye text-primary"></i>
                                        </button>
                                        <button class="btn btn-sm btn-light rounded-circle shadow-sm" 
                                                data-bs-toggle="modal" data-bs-target="#statusModal<?php echo $c['id']; ?>">
                                            <i class="fas fa-edit text-success"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Status Modal -->
                                <div class="modal fade" id="statusModal<?php echo $c['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header bg-light border-0">
                                                <h5 class="modal-title fw-bold"><?php echo __('update_ticket'); ?>: <?php echo $c['ticket_number']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body p-4">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="ticket_id" value="<?php echo $c['id']; ?>">
                                                    <label class="form-label fw-bold"><?php echo __('resolution_status'); ?></label>
                                                    <select name="status" class="form-select form-select-lg rounded-3 border-0 bg-light">
                                                        <option value="open" <?php echo $c['status'] == 'open' ? 'selected' : ''; ?>><?php echo __('open_status'); ?></option>
                                                        <option value="in_progress" <?php echo $c['status'] == 'in_progress' ? 'selected' : ''; ?>><?php echo __('in_progress_status'); ?></option>
                                                        <option value="resolved" <?php echo $c['status'] == 'resolved' ? 'selected' : ''; ?>><?php echo __('resolved_status'); ?></option>
                                                        <option value="closed" <?php echo $c['status'] == 'closed' ? 'selected' : ''; ?>><?php echo __('closed_status'); ?></option>
                                                    </select>
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                                                    <button type="submit" class="btn btn-primary rounded-pill px-4"><?php echo __('save'); ?></button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- View Modal -->
                                <div class="modal fade" id="viewModal<?php echo $c['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header border-0 pb-0">
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4 text-center">
                                                <div class="icon-circle bg-light text-primary mx-auto mb-3 slide-up" style="width: 80px; height: 80px; font-size: 2rem;"><i class="fas fa-ticket-alt"></i></div>
                                                <h4 class="fw-bold mb-1"><?php echo $c['ticket_number']; ?></h4>
                                                <?php echo getStatusBadge($c['status']); ?>
                                                <div class="text-start mt-4 bg-light p-4 rounded-4">
                                                    <p class="mb-2"><strong><?php echo __('subject'); ?>:</strong> <?php echo htmlspecialchars($c['subject']); ?></p>
                                                    <p class="mb-2"><strong><?php echo __('description'); ?>:</strong> <?php echo nl2br(htmlspecialchars($c['description'])); ?></p>
                                                    <hr>
                                                    <p class="mb-1 text-muted small"><i class="fas fa-user-circle me-2"></i><?php echo __('resident'); ?>: <?php echo $c['fname'] ? htmlspecialchars($c['fname'].' '.$c['lname']) : __('unknown'); ?></p>
                                                    <p class="mb-0 text-muted small"><i class="far fa-clock me-2"></i>Created: <?php echo date('M d, Y', strtotime($c['created_at'])); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Complaint Modal -->
<div class="modal fade" id="addComplaintModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-gradient-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i><?php echo __('register_service_request'); ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="add">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold"><?php echo __('resident_optional_id'); ?></label>
                            <input type="number" name="resident_id" class="form-control rounded-3" placeholder="Enter Resident DB ID if known">
                            <small class="text-muted">Leave blank for external or anonymous complaints.</small>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold"><?php echo __('subject'); ?></label>
                            <input type="text" name="subject" class="form-control rounded-3" required placeholder="e.g. Water shortage in block 4">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold"><?php echo __('priority'); ?></label>
                            <select name="priority" class="form-select rounded-3">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold"><?php echo __('detailed_description'); ?></label>
                            <textarea name="description" class="form-control rounded-3" rows="4" required placeholder="Describe the issue or request in detail..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5"><i class="fas fa-paper-plane me-2"></i><?php echo __('submit_ticket'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
