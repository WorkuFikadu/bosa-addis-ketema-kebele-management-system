<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

// Only allow admin access
if (($_SESSION['role'] ?? '') !== 'admin') {
    die("<div class='container py-5'><div class='alert alert-danger'>Access Denied. Administrators only.</div></div>");
}

$backup_dir = __DIR__ . '/../../backups/';
if (!is_dir($backup_dir)) mkdir($backup_dir, 0777, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'backup') {
        $filename = 'backup_kebele_' . date('Y_m_d_H_i_s') . '.sql';
        $filepath = $backup_dir . $filename;
        
        // Ensure accurate mysqldump path for XAMPP
        $mysqldump = 'c:\xampp\mysql\bin\mysqldump.exe';
        
        // Run mysqldump command
        $cmd = "\"$mysqldump\" -u root kebele_system > \"$filepath\" 2>&1";
        exec($cmd, $output, $return_var);
        
        if ($return_var === 0) {
            $success = "Database backup created successfully: $filename";
            log_activity($pdo, $_SESSION['user_id'] ?? null, "Created Database Backup: $filename");
            
            // Insert notification for admin
            $pdo->prepare("INSERT INTO notifications (user_id, type, message, link) VALUES (?, 'backup', ?, '#')")
                ->execute([$_SESSION['user_id'], "Backup Successful: $filename"]);
        } else {
            $error = "Backup failed. Make sure mysqldump is accessible. Output: " . implode(" ", $output);
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $file = basename($_POST['file']);
        if (file_exists($backup_dir . $file)) {
            unlink($backup_dir . $file);
            $success = "Backup deleted successfully.";
            log_activity($pdo, $_SESSION['user_id'] ?? null, "Deleted Database Backup: $file");
        }
    }
}

// Fetch all backups
$backups = [];
$files = array_diff(scandir($backup_dir), ['.', '..']);
foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
        $backups[] = [
            'name' => $file,
            'size' => round(filesize($backup_dir . $file) / 1024 / 1024, 2) . ' MB',
            'date' => date('F d, Y h:i A', filemtime($backup_dir . $file))
        ];
    }
}
usort($backups, function($a, $b) { return strtotime($b['date']) - strtotime($a['date']); });
?>

<div class="container py-5">
    <div class="row">
        <!-- Main Backup Action Card -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-lg" style="border-radius: 20px; background: linear-gradient(135deg, #1e293b, #0f172a);">
                <div class="card-body p-4 text-center text-white">
                    <div class="mb-4 mt-3">
                        <i class="fas fa-database fa-4x text-info" style="filter: drop-shadow(0 0 15px rgba(13,202,240,0.5));"></i>
                    </div>
                    <h3 class="fw-bold mb-3">System Backup</h3>
                    <p class="text-white-50 mb-4 small" style="line-height: 1.6;">Create a highly-secure snapshot of the entire Kebele database. This includes all residents, cards, houses, and administrative documents.</p>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="backup">
                        <button type="submit" class="btn btn-info btn-lg w-100 rounded-pill fw-bold text-dark shadow-sm">
                            <i class="fas fa-cloud-download-alt me-2"></i> Create Snapshot Now
                        </button>
                    </form>
                    
                    <div class="mt-4 pt-4 border-top border-secondary text-start text-white-50 small">
                        <p class="mb-1"><i class="fas fa-check-circle text-success me-2"></i> All Tables Included</p>
                        <p class="mb-1"><i class="fas fa-check-circle text-success me-2"></i> Instant Downloadable SQL</p>
                        <p class="mb-0"><i class="fas fa-check-circle text-success me-2"></i> Immutable Logs Preserved</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backup History -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold text-dark"><i class="fas fa-history me-2 text-primary"></i>Snapshot Availability Engine</h5>
                    <p class="text-muted small">Manage previously generated database snapshots.</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="px-4 mt-2">
                        <div class="alert alert-success border-0 rounded-3 shadow-sm py-2"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?></div>
                    </div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="px-4 mt-2">
                        <div class="alert alert-danger border-0 rounded-3 shadow-sm py-2"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?></div>
                    </div>
                <?php endif; ?>

                <div class="card-body px-4 pb-4 pt-2">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3 border-0 rounded-start-3">Snapshot File</th>
                                    <th class="border-0">Date Created</th>
                                    <th class="border-0">Size</th>
                                    <th class="border-0 rounded-end-3 text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($backups)): ?>
                                    <tr><td colspan="4" class="text-center py-5 text-muted"><i class="fas fa-bed fa-2x mb-2 d-block"></i>No backups available.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($backups as $b): ?>
                                        <tr>
                                            <td class="ps-3 fw-bold text-dark font-monospace" style="font-size: 0.85rem;"><i class="fas fa-file-code text-muted me-2"></i><?php echo $b['name']; ?></td>
                                            <td class="text-muted"><i class="far fa-clock me-1"></i><?php echo $b['date']; ?></td>
                                            <td><span class="badge bg-light text-dark border"><?php echo $b['size']; ?></span></td>
                                            <td class="text-end pe-3">
                                                <a href="/Bosa Addis/backups/<?php echo urlencode($b['name']); ?>" class="btn btn-sm btn-outline-primary rounded-circle me-1" title="Download" download>
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this snapshot permanently?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="file" value="<?php echo htmlspecialchars($b['name']); ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
