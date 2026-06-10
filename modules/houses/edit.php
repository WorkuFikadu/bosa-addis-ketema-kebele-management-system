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
    
    $house_type        = $_POST['house_type'];
    $construction_type = $_POST['construction_type'];
    $rooms_count       = (int)$_POST['rooms_count'];
    $floor_type        = $_POST['floor_type'];
    $roof_type         = $_POST['roof_type'];
    $has_water         = $_POST['has_water'];
    $has_electricity   = $_POST['has_electricity'];
    $toilet_type       = $_POST['toilet_type'];
    $constructed_year  = (int)$_POST['constructed_year'] ?: null;
    $block_no          = $_POST['block_no'];

    // Handle File Upload
    $plan_cert = $house['plan_certificate'];
    if (isset($_FILES['plan_certificate']) && $_FILES['plan_certificate']['error'] === 0) {
        $upload_dir = "../../uploads/houses/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $ext = pathinfo($_FILES['plan_certificate']['name'], PATHINFO_EXTENSION);
        $plan_cert = time() . '_plan_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES['plan_certificate']['tmp_name'], $upload_dir . $plan_cert)) {
            // Success
        } else {
            $plan_cert = $house['plan_certificate'];
        }
    }

    $sql_update = "UPDATE houses SET 
                    area = ?, door = ?, owner_id = ?, owner_individual_id = ?, 
                    house_type = ?, construction_type = ?, rooms_count = ?, 
                    floor_type = ?, roof_type = ?, has_water = ?, 
                    has_electricity = ?, toilet_type = ?, constructed_year = ?, block_no = ?,
                    plan_certificate = ?
                   WHERE hnum = ?";
    $stmt_update = $pdo->prepare($sql_update);
    try {
        $stmt_update->execute([
            $area, $door, $owner_id, $owner_individual_id, 
            $house_type, $construction_type, $rooms_count, 
            $floor_type, $roof_type, $has_water, 
            $has_electricity, $toilet_type, $constructed_year, $block_no, 
            $plan_cert,
            $hnum
        ]);
        $success = "House H-{$hnum} record updated successfully with full details!";
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
    <h2><i class="fas fa-edit me-2 text-info"></i>Edit Detailed House H-<?php echo $hnum; ?></h2>
    <div class="d-flex gap-2">
        <a href="view.php?hnum=<?php echo $hnum; ?>" class="btn btn-outline-primary"><i class="fas fa-eye me-1"></i>View Details</a>
        <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success shadow-sm border-0"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger shadow-sm border-0"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="row g-4">
    <!-- Main Form Section -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <h5 class="mb-0 text-primary"><i class="fas fa-home me-2"></i>Physical Property Information</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">House Number</label>
                        <input type="text" class="form-control bg-light" value="H-<?php echo $hnum; ?>" disabled>
                        <div class="form-text small">Unique Identifier (Read-only)</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Block Number</label>
                        <input type="text" name="block_no" class="form-control" value="<?php echo htmlspecialchars($house['block_no'] ?? ''); ?>" placeholder="e.g. B-12">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Year Constructed</label>
                        <input type="number" name="constructed_year" class="form-control" value="<?php echo $house['constructed_year']; ?>" placeholder="e.g. 2015">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">House Type <span class="text-danger">*</span></label>
                        <select name="house_type" class="form-select" required>
                            <?php 
                            $types = ['Residential', 'Commercial', 'Mixed', 'Public', 'Religious', 'Other'];
                            foreach($types as $t): 
                            ?>
                                <option value="<?php echo $t; ?>" <?php echo ($house['house_type'] == $t) ? 'selected' : ''; ?>><?php echo $t; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Construction Type <span class="text-danger">*</span></label>
                        <select name="construction_type" class="form-select" required>
                            <?php 
                            $c_types = ['Wood and Mud', 'Stone and Cement', 'Brick', 'Hollow Block', 'Modern Concrete', 'Other'];
                            foreach($c_types as $ct): 
                            ?>
                                <option value="<?php echo $ct; ?>" <?php echo ($house['construction_type'] == $ct) ? 'selected' : ''; ?>><?php echo $ct; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Area (m²) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="area" class="form-control" value="<?php echo $house['area']; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Number of Rooms <span class="text-danger">*</span></label>
                        <input type="number" name="rooms_count" class="form-control" value="<?php echo $house['rooms_count'] ?: 1; ?>" required min="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Number of Doors <span class="text-danger">*</span></label>
                        <input type="number" name="door" class="form-control" value="<?php echo $house['door']; ?>" required min="1">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Floor Type</label>
                        <select name="floor_type" class="form-select">
                            <?php 
                            $floors = ['Earth', 'Cement', 'Tiles', 'Wood', 'Marble'];
                            foreach($floors as $f): 
                            ?>
                                <option value="<?php echo $f; ?>" <?php echo ($house['floor_type'] == $f) ? 'selected' : ''; ?>><?php echo $f; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Roof Type</label>
                        <select name="roof_type" class="form-select">
                            <?php 
                            $roofs = ['CIS', 'Thatch', 'Concrete Slab', 'Tiles'];
                            foreach($roofs as $r): 
                            ?>
                                <option value="<?php echo $r; ?>" <?php echo ($house['roof_type'] == $r) ? 'selected' : ''; ?>><?php echo ($r == 'CIS' ? 'Corrugated Iron Sheet (CIS)' : $r); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <h5 class="mb-0 text-info"><i class="fas fa-plug me-2"></i>Utilities & Sanitation</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Water Supply <span class="text-danger">*</span></label>
                        <select name="has_water" class="form-select" required>
                            <option value="No" <?php echo ($house['has_water'] == 'No') ? 'selected' : ''; ?>>No Connection</option>
                            <option value="Yes" <?php echo ($house['has_water'] == 'Yes') ? 'selected' : ''; ?>>Yes, Connected</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Electricity <span class="text-danger">*</span></label>
                        <select name="has_electricity" class="form-select" required>
                            <option value="No" <?php echo ($house['has_electricity'] == 'No') ? 'selected' : ''; ?>>No Connection</option>
                            <option value="Yes" <?php echo ($house['has_electricity'] == 'Yes') ? 'selected' : ''; ?>>Yes, Connected</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Toilet Facility <span class="text-danger">*</span></label>
                        <select name="toilet_type" class="form-select" required>
                            <?php 
                            $toilets = ['None', 'Pit Latrine', 'Flush Toilet', 'Shared'];
                            foreach($toilets as $t): 
                            ?>
                                <option value="<?php echo $t; ?>" <?php echo ($house['toilet_type'] == $t) ? 'selected' : ''; ?>><?php echo $t; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Section -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <h5 class="mb-0 text-warning"><i class="fas fa-file-contract me-2"></i>Documentation</h5>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">House/Land Plan Certificate</label>
                    <input type="file" name="plan_certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text small">Accepted: PDF, JPG, PNG (Max 5MB)</div>
                </div>
                <?php if ($house['plan_certificate']): ?>
                    <div class="mt-3 p-3 bg-light rounded d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-file-pdf text-danger fs-4"></i>
                            <div>
                                <div class="small fw-bold">Current Document</div>
                                <div class="text-muted" style="font-size: 10px;"><?php echo $house['plan_certificate']; ?></div>
                            </div>
                        </div>
                        <a href="../../uploads/houses/<?php echo $house['plan_certificate']; ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                            <i class="fas fa-download me-1"></i>View
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-success text-white py-3">
                <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>Ownership</h5>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Owner (Registered Resident)</label>
                    <select name="owner_individual_id" class="form-select" id="ownerSelect">
                        <option value="">— Change Owner —</option>
                        <?php foreach ($residents as $r): ?>
                            <option value="<?php echo $r['id']; ?>"
                                <?php echo ($house['owner_individual_id'] == $r['id']) ? 'selected' : ''; ?>
                                data-name="<?php echo htmlspecialchars("{$r['fname']} {$r['lname']}"); ?>"
                                data-occ="<?php echo htmlspecialchars($r['occ'] ?? ''); ?>"
                                data-age="<?php echo $r['age'] ?? ''; ?>">
                                <?php echo htmlspecialchars("{$r['fname']} {$r['mname']} {$r['lname']}"); ?> (ID: <?php echo $r['id']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="ownerPreview" class="<?php echo $current_owner ? '' : 'd-none'; ?> mt-2 p-3 bg-light rounded border border-success">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                            <i class="fas fa-check small"></i>
                        </div>
                        <div class="fw-bold small" id="previewName"><?php echo $current_owner ? htmlspecialchars("{$current_owner['fname']} {$current_owner['lname']}") : ''; ?></div>
                    </div>
                    <div class="small text-muted" id="previewMeta"><?php echo $current_owner ? htmlspecialchars($current_owner['occ'] ?? '') . ($current_owner['age'] ? ' · '.$current_owner['age'].' yrs' : '') : ''; ?></div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-info text-white w-100 py-3 fw-bold">
                        <i class="fas fa-save me-2"></i>Save All Changes
                    </button>
                    <a href="view.php?hnum=<?php echo $hnum; ?>" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
                </div>
            </div>
        </div>

        <?php if ($current_owner): ?>
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="bg-primary py-4 text-center">
                <img src="../../assets/images/<?php echo htmlspecialchars($current_owner['phot'] ?? 'default_profile.png'); ?>"
                     class="rounded-circle border border-3 border-white"
                     style="width:80px;height:80px;object-fit:cover;"
                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode("{$current_owner['fname']} {$current_owner['lname']}"); ?>&size=160&background=fff&color=0d6efd'">
                <h6 class="text-white mt-2 mb-0"><?php echo htmlspecialchars("{$current_owner['fname']} {$current_owner['lname']}"); ?></h6>
                <small class="text-white-50">Current Owner</small>
            </div>
            <div class="card-body p-3">
                <div class="d-grid gap-1">
                    <a href="../residents/view.php?id=<?php echo $current_owner['id']; ?>" class="btn btn-sm btn-link text-decoration-none">
                        <i class="fas fa-external-link-alt me-1"></i>View Resident Profile
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</form>

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
