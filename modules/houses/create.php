<?php
// modules/houses/create.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

// Fetch all registered residents for the owner dropdown
$residents = $pdo->query("
    SELECT i.id, i.fname, i.lname, i.mname, i.occ, ag.age
    FROM individuals i
    LEFT JOIN ages ag ON i.id = ag.id
    WHERE i.status = 'alive'
    ORDER BY i.fname, i.lname
")->fetchAll();

$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hnum              = (int)$_POST['hnum'];
    $area              = (float)$_POST['area'];
    $door              = (int)$_POST['door'];
    $owner_individual_id = $_POST['owner_individual_id'] ?: null;
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

    // Derive legacy owner_id label from individual if selected
    $owner_id = $owner_individual_id ? 'RES-' . $owner_individual_id : ($_POST['owner_label'] ?? 'UNLINKED');

    $sql = "INSERT INTO houses (hnum, area, door, owner_id, owner_individual_id, house_type, construction_type, rooms_count, floor_type, roof_type, has_water, has_electricity, toilet_type, constructed_year, block_no) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute([
            $hnum, $area, $door, $owner_id, $owner_individual_id, 
            $house_type, $construction_type, $rooms_count, $floor_type, $roof_type, 
            $has_water, $has_electricity, $toilet_type, $constructed_year, $block_no
        ]);
        $success = "House H-{$hnum} added successfully with full details!";
    } catch (PDOException $e) {
        $error = "Failed to add house: " . $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus-circle me-2 text-primary"></i>Register Detailed House</h2>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Houses</a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success shadow-sm border-0"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger shadow-sm border-0"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" class="row g-4">
    <!-- Main Form Section -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <h5 class="mb-0 text-primary"><i class="fas fa-home me-2"></i>Basic & Physical Information</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">House Number <span class="text-danger">*</span></label>
                        <input type="number" name="hnum" class="form-control" required placeholder="e.g. 105">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Block Number</label>
                        <input type="text" name="block_no" class="form-control" placeholder="e.g. B-12">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Year Constructed</label>
                        <input type="number" name="constructed_year" class="form-control" placeholder="e.g. 2015" min="1900" max="<?php echo date('Y'); ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">House Type <span class="text-danger">*</span></label>
                        <select name="house_type" class="form-select" required>
                            <option value="Residential">Residential</option>
                            <option value="Commercial">Commercial</option>
                            <option value="Mixed">Mixed (Residential & Commercial)</option>
                            <option value="Public">Public/Government</option>
                            <option value="Religious">Religious</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Construction Type <span class="text-danger">*</span></label>
                        <select name="construction_type" class="form-select" required>
                            <option value="Wood and Mud">Wood and Mud (Chika-bet)</option>
                            <option value="Stone and Cement">Stone and Cement</option>
                            <option value="Brick">Brick</option>
                            <option value="Hollow Block">Hollow Block (HCB)</option>
                            <option value="Modern Concrete">Modern Concrete</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Total Area (m²) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="area" class="form-control" required placeholder="e.g. 120.5">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Number of Rooms <span class="text-danger">*</span></label>
                        <input type="number" name="rooms_count" class="form-control" required value="1" min="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Number of Doors <span class="text-danger">*</span></label>
                        <input type="number" name="door" class="form-control" required value="1" min="1">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Floor Type</label>
                        <select name="floor_type" class="form-select">
                            <option value="Earth">Earth/Mud</option>
                            <option value="Cement">Cement</option>
                            <option value="Tiles">Tiles/Ceramic</option>
                            <option value="Wood">Wood</option>
                            <option value="Marble">Marble</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Roof Type</label>
                        <select name="roof_type" class="form-select">
                            <option value="CIS">Corrugated Iron Sheet (CIS)</option>
                            <option value="Thatch">Thatch/Grass</option>
                            <option value="Concrete Slab">Concrete Slab</option>
                            <option value="Tiles">Roof Tiles</option>
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
                            <option value="No">No Connection</option>
                            <option value="Yes">Yes, Connected</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Electricity <span class="text-danger">*</span></label>
                        <select name="has_electricity" class="form-select" required>
                            <option value="No">No Connection</option>
                            <option value="Yes">Yes, Connected</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Toilet Facility <span class="text-danger">*</span></label>
                        <select name="toilet_type" class="form-select" required>
                            <option value="None">None</option>
                            <option value="Pit Latrine">Private Pit Latrine</option>
                            <option value="Flush Toilet">Private Flush Toilet</option>
                            <option value="Shared">Shared Community Toilet</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Section: Owner Linking -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4 bg-light">
            <div class="card-header bg-dark text-white py-3">
                <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>Ownership Link</h5>
            </div>
            <div class="card-body p-4">
                <?php if (empty($residents)): ?>
                    <div class="alert alert-warning small">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No residents found. <a href="../residents/create.php" class="fw-bold">Register a resident first</a>.
                    </div>
                <?php else: ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Resident Owner <span class="text-danger">*</span></label>
                        <select name="owner_individual_id" class="form-select" required id="ownerSelect">
                            <option value="">— Search Resident —</option>
                            <?php foreach ($residents as $r): ?>
                                <option value="<?php echo $r['id']; ?>"
                                        data-name="<?php echo htmlspecialchars("{$r['fname']} {$r['lname']}"); ?>"
                                        data-occ="<?php echo htmlspecialchars($r['occ'] ?? ''); ?>"
                                        data-age="<?php echo $r['age'] ?? ''; ?>">
                                    <?php echo htmlspecialchars("{$r['fname']} {$r['mname']} {$r['lname']}"); ?> (ID: <?php echo $r['id']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Live Preview Card -->
                    <div id="ownerPreview" class="d-none mt-3 p-3 bg-white rounded border border-success">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                                <i class="fas fa-check small"></i>
                            </div>
                            <div class="fw-bold small" id="previewName"></div>
                        </div>
                        <div class="small text-muted" id="previewMeta"></div>
                    </div>
                <?php endif; ?>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold">
                        <i class="fas fa-save me-2"></i>Register Property
                    </button>
                    <p class="text-center mt-3 small text-muted">
                        <i class="fas fa-info-circle me-1"></i> Ensure all property details are verified with land records.
                    </p>
                </div>
            </div>
        </div>
    </div>
</form>


<script>
document.getElementById('ownerSelect')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const preview = document.getElementById('ownerPreview');
    const nameEl  = document.getElementById('previewName');
    const metaEl  = document.getElementById('previewMeta');
    if (this.value) {
        nameEl.textContent = opt.dataset.name;
        metaEl.textContent = (opt.dataset.occ || 'N/A') + (opt.dataset.age ? '  ·  ' + opt.dataset.age + ' years old' : '');
        preview.classList.remove('d-none');
    } else {
        preview.classList.add('d-none');
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
