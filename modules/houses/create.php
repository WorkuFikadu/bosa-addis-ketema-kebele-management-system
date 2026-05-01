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

    // Derive legacy owner_id label from individual if selected
    $owner_id = $owner_individual_id ? 'RES-' . $owner_individual_id : ($_POST['owner_label'] ?? 'UNLINKED');

    $stmt = $pdo->prepare("INSERT INTO houses (hnum, area, door, owner_id, owner_individual_id) VALUES (?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$hnum, $area, $door, $owner_id, $owner_individual_id]);
        $success = "House H-{$hnum} added successfully and linked to the selected resident!";
    } catch (PDOException $e) {
        $error = "Failed to add house: " . $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus-circle me-2 text-primary"></i>Register New House</h2>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Houses</a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Form -->
    <div class="col-md-7">
        <form method="POST" class="card border-0 shadow-sm p-4">
            <h5 class="border-bottom pb-3 mb-4 text-primary"><i class="fas fa-home me-2"></i>Property Details</h5>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">House Number <span class="text-danger">*</span></label>
                    <input type="number" name="hnum" class="form-control" required placeholder="e.g. 105">
                    <div class="form-text">Must be a unique kebele-assigned number.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Area (m²) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="area" class="form-control" required placeholder="e.g. 120.5">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Number of Doors <span class="text-danger">*</span></label>
                    <input type="number" name="door" class="form-control" required value="1" min="1">
                </div>
            </div>

            <h5 class="border-bottom pb-3 mb-4 text-success"><i class="fas fa-user-shield me-2"></i>Assign Owner (Registered Resident)</h5>

            <?php if (empty($residents)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No registered residents found. <a href="../residents/create.php" class="fw-bold">Register a resident first</a> before assigning house ownership.
                </div>
            <?php else: ?>
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Resident Owner <span class="text-danger">*</span></label>
                    <select name="owner_individual_id" class="form-select" required id="ownerSelect">
                        <option value="">— Search & Select Registered Resident —</option>
                        <?php foreach ($residents as $r): ?>
                            <option value="<?php echo $r['id']; ?>"
                                    data-name="<?php echo htmlspecialchars("{$r['fname']} {$r['lname']}"); ?>"
                                    data-occ="<?php echo htmlspecialchars($r['occ'] ?? ''); ?>"
                                    data-age="<?php echo $r['age'] ?? ''; ?>">
                                <?php echo htmlspecialchars("{$r['fname']} {$r['mname']} {$r['lname']}"); ?>
                                (ID: #<?php echo $r['id']; ?> · <?php echo htmlspecialchars($r['occ'] ?? 'N/A'); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Only alive registered residents are listed.</div>
                </div>

                <!-- Live Preview Card -->
                <div id="ownerPreview" class="d-none mt-3 p-3 bg-light rounded border border-success border-opacity-50">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0;">
                            <i class="fas fa-user fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark" id="previewName"></div>
                            <small class="text-muted" id="previewMeta"></small>
                        </div>
                        <span class="ms-auto badge bg-success">Owner</span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mt-4">
                <button type="submit" class="btn btn-success w-100 py-3 fw-bold fs-6">
                    <i class="fas fa-save me-2"></i>Save House Record
                </button>
            </div>
        </form>
    </div>

    <!-- Info Panel -->
    <div class="col-md-5">
        <div class="card border-0 shadow-sm p-4 h-100" style="background: linear-gradient(135deg, #0f172a, #1e3a8a); color: white;">
            <h5 class="mb-4"><i class="fas fa-info-circle me-2 text-sky-400"></i>How House Registration Works</h5>
            <ul class="list-unstyled" style="line-height: 2.2;">
                <li><i class="fas fa-check-circle text-success me-2"></i>House is assigned to a <strong>registered resident</strong></li>
                <li><i class="fas fa-check-circle text-success me-2"></i>Owner's full profile is automatically linked</li>
                <li><i class="fas fa-check-circle text-success me-2"></i>House number must match kebele land records</li>
                <li><i class="fas fa-check-circle text-success me-2"></i>Owner can be changed later via Edit</li>
                <li><i class="fas fa-link text-warning me-2"></i>Resident not listed? <a href="../residents/create.php" class="text-warning fw-bold">Register them first</a></li>
            </ul>
            <hr class="border-white border-opacity-25 my-4">
            <p class="small text-white-50 mb-0">All house ownership records are permanently archived in the Kebele land registry database.</p>
        </div>
    </div>
</div>

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
