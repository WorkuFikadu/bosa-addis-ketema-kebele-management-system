<?php
// modules/houses/edit.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$hnum = $_GET['hnum'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM houses WHERE hnum = ?");
$stmt->execute([$hnum]);
$house = $stmt->fetch();

if (!$house) {
    header('Location: index.php');
    exit;
}

// All alive registered residents
$residents = $pdo->query("
    SELECT i.id, i.fname, i.lname, i.mname, i.occ, ag.age
    FROM individuals i
    LEFT JOIN ages ag ON i.id = ag.id
    WHERE i.status = 'alive'
    ORDER BY i.fname, i.lname
")->fetchAll();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $area              = (float)$_POST['area'];
    $door              = (int)$_POST['door'];
    $owner_individual_id = $_POST['owner_individual_id'] ?: null;
    $owner_id          = $owner_individual_id ? 'RES-' . $owner_individual_id : ($house['owner_id'] ?? 'UNLINKED');

    $stmt_update = $pdo->prepare("UPDATE houses SET area = ?, door = ?, owner_id = ?, owner_individual_id = ? WHERE hnum = ?");
    try {
        $stmt_update->execute([$area, $door, $owner_id, $owner_individual_id, $hnum]);
        $success = "House H-{$hnum} updated successfully!";
        // Refresh
        $stmt->execute([$hnum]);
        $house = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Update failed: " . $e->getMessage();
    }
}

// Currently linked owner
$current_owner = null;
if ($house['owner_individual_id']) {
    $own_stmt = $pdo->prepare("SELECT i.*, ag.age, ag.bdate FROM individuals i LEFT JOIN ages ag ON i.id = ag.id WHERE i.id = ?");
    $own_stmt->execute([$house['owner_individual_id']]);
    $current_owner = $own_stmt->fetch();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit me-2 text-info"></i>Edit House H-<?php echo $hnum; ?></h2>
    <div class="d-flex gap-2">
        <a href="view.php?hnum=<?php echo $hnum; ?>" class="btn btn-outline-primary"><i class="fas fa-eye me-1"></i>View</a>
        <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div><?php endif; ?>

<div class="row g-4">
    <div class="col-md-7">
        <form method="POST" class="card border-0 shadow-sm p-4">
            <h5 class="border-bottom pb-3 mb-4 text-primary"><i class="fas fa-home me-2"></i>Property Details</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">House Number</label>
                    <input type="text" class="form-control bg-light" value="H-<?php echo $hnum; ?>" disabled>
                    <div class="form-text">House number cannot be changed.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Area (m²) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="area" class="form-control" value="<?php echo $house['area']; ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Number of Doors <span class="text-danger">*</span></label>
                    <input type="number" name="door" class="form-control" value="<?php echo $house['door']; ?>" required>
                </div>
            </div>

            <h5 class="border-bottom pb-3 mb-4 text-success"><i class="fas fa-user-shield me-2"></i>Change Owner</h5>
            <div class="mb-3">
                <label class="form-label fw-bold">Select New Resident Owner</label>
                <select name="owner_individual_id" class="form-select" id="ownerSelect">
                    <option value="">— Keep current or select new owner —</option>
                    <?php foreach ($residents as $r): ?>
                        <option value="<?php echo $r['id']; ?>"
                            <?php echo ($house['owner_individual_id'] == $r['id']) ? 'selected' : ''; ?>
                            data-name="<?php echo htmlspecialchars("{$r['fname']} {$r['lname']}"); ?>"
                            data-occ="<?php echo htmlspecialchars($r['occ'] ?? ''); ?>"
                            data-age="<?php echo $r['age'] ?? ''; ?>">
                            <?php echo htmlspecialchars("{$r['fname']} {$r['mname']} {$r['lname']}"); ?>
                            (ID: #<?php echo $r['id']; ?> · <?php echo htmlspecialchars($r['occ'] ?? 'N/A'); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="ownerPreview" class="<?php echo $current_owner ? '' : 'd-none'; ?> mt-2 p-3 bg-light rounded border border-success border-opacity-50">
                <div class="d-flex align-items-center gap-3">
                    <img src="../../assets/images/<?php echo htmlspecialchars($current_owner['phot'] ?? 'default_profile.png'); ?>"
                         class="rounded-circle border" style="width:48px;height:48px;object-fit:cover;"
                         onerror="this.src='https://ui-avatars.com/api/?name=Owner&size=80&background=4f46e5&color=fff'"
                         id="previewImg">
                    <div>
                        <div class="fw-bold text-dark" id="previewName"><?php echo $current_owner ? htmlspecialchars("{$current_owner['fname']} {$current_owner['lname']}") : ''; ?></div>
                        <small class="text-muted" id="previewMeta"><?php echo $current_owner ? htmlspecialchars($current_owner['occ'] ?? '') . ($current_owner['age'] ? ' · '.$current_owner['age'].' yrs' : '') : ''; ?></small>
                    </div>
                    <span class="ms-auto badge bg-success">Owner</span>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-info text-white w-100 py-3 fw-bold fs-6">
                    <i class="fas fa-save me-2"></i>Update House Record
                </button>
            </div>
        </form>
    </div>

    <!-- Current Owner Panel -->
    <div class="col-md-5">
        <div class="card border-0 shadow-sm p-4">
            <h6 class="fw-bold mb-3 text-muted text-uppercase" style="letter-spacing:1px; font-size:0.75rem;">Current Owner</h6>
            <?php if ($current_owner): ?>
                <div class="text-center mb-4">
                    <img src="../../assets/images/<?php echo htmlspecialchars($current_owner['phot'] ?? 'default_profile.png'); ?>"
                         class="rounded-circle border border-3 border-primary mb-3"
                         style="width:100px;height:100px;object-fit:cover;"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode("{$current_owner['fname']} {$current_owner['lname']}"); ?>&size=200&background=4f46e5&color=fff'">
                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars("{$current_owner['fname']} {$current_owner['mname']} {$current_owner['lname']}"); ?></h5>
                    <span class="badge bg-primary"><?php echo htmlspecialchars($current_owner['occ'] ?? 'N/A'); ?></span>
                    <span class="badge bg-secondary ms-1"><?php echo $current_owner['age'] ?? '?'; ?> yrs</span>
                </div>
                <table class="table table-sm">
                    <tr><td class="text-muted">Resident ID</td><td class="fw-bold">#<?php echo $current_owner['id']; ?></td></tr>
                    <tr><td class="text-muted">Birth Date</td><td><?php echo $current_owner['bdate'] ? date('M d, Y', strtotime($current_owner['bdate'])) : 'N/A'; ?></td></tr>
                    <tr><td class="text-muted">Marital</td><td><?php echo htmlspecialchars($current_owner['mar'] ?? 'N/A'); ?></td></tr>
                    <tr><td class="text-muted">Nationality</td><td><?php echo htmlspecialchars($current_owner['nat'] ?? 'N/A'); ?></td></tr>
                </table>
                <a href="../residents/view.php?id=<?php echo $current_owner['id']; ?>" class="btn btn-outline-primary w-100">
                    <i class="fas fa-id-card me-2"></i>View Full Profile
                </a>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No resident is currently linked as owner.</p>
                    <p class="small text-warning">Legacy owner ID: <strong><?php echo htmlspecialchars($house['owner_id'] ?? 'N/A'); ?></strong></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('ownerSelect')?.addEventListener('change', function() {
    const opt   = this.options[this.selectedIndex];
    const preview = document.getElementById('ownerPreview');
    const nameEl  = document.getElementById('previewName');
    const metaEl  = document.getElementById('previewMeta');
    if (this.value) {
        nameEl.textContent = opt.dataset.name;
        metaEl.textContent = (opt.dataset.occ || 'N/A') + (opt.dataset.age ? '  ·  ' + opt.dataset.age + ' yrs' : '');
        preview.classList.remove('d-none');
    } else {
        preview.classList.add('d-none');
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
