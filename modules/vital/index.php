<?php
// modules/vital/index.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

// Fetch certificates
$query = "SELECT vc.*, i.fname, i.lname, i.status 
          FROM vital_certificates vc 
          JOIN individuals i ON vc.resident_id = i.id 
          ORDER BY vc.issue_date DESC";
$certs = $pdo->query($query)->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-signature me-2 text-primary"></i><?php echo __('vital_records'); ?></h2>
    <div class="d-flex gap-2 flex-wrap">
        <a href="issue_birth.php" class="btn btn-primary">
            <i class="fas fa-baby me-2"></i><?php echo __('birth_cert'); ?>
        </a>
        <a href="issue_death.php" class="btn btn-danger">
            <i class="fas fa-skull me-2"></i><?php echo __('death_cert'); ?>
        </a>
        <a href="issue_clearance.php" class="btn btn-success">
            <i class="fas fa-file-check me-2"></i><?php echo __('clearance_cert'); ?>
        </a>
        <a href="issue_marriage.php" class="btn btn-warning text-dark">
            <i class="fas fa-heart me-2"></i>Marriage Certificate
        </a>
        <a href="issue_divorce.php" class="btn btn-secondary">
            <i class="fas fa-heart-broken me-2"></i>Divorce Certificate
        </a>
    </div>
</div>

<div class="card p-4 border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="text-muted small text-uppercase">
                <tr>
                    <th class="border-0"><?php echo __('cert_no'); ?></th>
                    <th class="border-0"><?php echo __('resident'); ?></th>
                    <th class="border-0"><?php echo __('type'); ?></th>
                    <th class="border-0"><?php echo __('issue_date'); ?></th>
                    <th class="border-0 text-end"><?php echo __('action'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($certs)): ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted"><?php echo __('no_records'); ?></td></tr>
                <?php else: ?>
                    <?php foreach ($certs as $c): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?php echo $c['cert_number']; ?></td>
                            <td><?php echo "{$c['fname']} {$c['lname']}"; ?></td>
                            <td>
                                <?php
                                    $badge_map = [
                                        'birth' => ['bg-info', __('birth_cert')],
                                        'death' => ['bg-danger', __('death_cert')],
                                        'clearance' => ['bg-success', __('clearance_cert')],
                                        'marriage' => ['bg-warning text-dark', 'Marriage'],
                                        'divorce' => ['bg-secondary', 'Divorce'],
                                    ];
                                    $bt = $badge_map[$c['cert_type']] ?? ['bg-secondary', $c['cert_type']];
                                ?>
                                <span class="badge <?php echo $bt[0]; ?>">
                                    <?php echo $bt[1]; ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($c['issue_date'])); ?></td>
                            <td class="text-end">
                                <a href="print.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-light border" target="_blank">
                                    <i class="fas fa-print me-1"></i> <?php echo __('print'); ?>
                                </a>
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <a href="delete.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i> <?php echo __('delete'); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
