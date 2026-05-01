<?php
// modules/idcards/generate.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

// Get residents who don't have an ID card yet
$residents = $pdo->query("SELECT id, fname, lname FROM individuals WHERE id NOT IN (SELECT resident_id FROM id_cards) ORDER BY fname")->fetchAll();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resident_id = $_POST['resident_id'];
    $issue_date = date('Y-m-d');
    
    $year = date('Y');
    
    // Get the last sequential number
    $lastIdStmt = $pdo->prepare("SELECT id_num FROM id_cards WHERE id_num LIKE 'IB%' ORDER BY id DESC LIMIT 1");
    $lastIdStmt->execute();
    $lastId = $lastIdStmt->fetchColumn();
    
    if ($lastId) {
        $lastNumber = intval(substr($lastId, 2));
        $nextNumber = $lastNumber + 1;
    } else {
        $nextNumber = 0;
    }
    
    $sequence = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    $id_num = "IB$sequence";
    $expiry_date = date('Y-m-d', strtotime('+5 years'));

    $stmt = $pdo->prepare("INSERT INTO id_cards (resident_id, id_num, issue_date, expiry_date) VALUES (?, ?, ?, ?)");
    try {
        $stmt->execute([$resident_id, $id_num, $issue_date, $expiry_date]);
        $id_card_db_id = $pdo->lastInsertId();
        $success = "ID Card generated successfully! ID Number: $id_num";
    } catch (PDOException $e) {
        $error = "Failed to generate ID card: " . $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Issue New ID Card</h2>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success">
        <?php echo $success; ?> 
        <a href="print.php?id=<?php echo $id_card_db_id; ?>" class="btn btn-sm btn-success ms-3" target="_blank">Print Now</a>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <form method="POST" class="card p-4 shadow-sm">
            <div class="mb-4">
                <label class="form-label fw-bold">Select Resident</label>
                <select name="resident_id" class="form-select" required>
                    <option value="">-- Choose Resident --</option>
                    <?php foreach ($residents as $r): ?>
                        <option value="<?php echo $r['id']; ?>"><?php echo "{$r['fname']} {$r['lname']} (ID: #{$r['id']})"; ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text mt-2 text-warning">Only residents who do not have an ID card are listed here.</div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">
                <i class="fas fa-id-card me-2"></i>Generate ID Card
            </button>
        </form>
    </div>
    <div class="col-md-6">
        <div class="alert alert-info">
            <h5><i class="fas fa-info-circle me-2"></i>ID Card Logic</h5>
            <p>ID cards are generated <strong>sequentially</strong> with a unique identification number following the <code>IBXX</code> pattern (e.g., IB00, IB01).</p>
            <p>Once generated, you can format and print the ID card with the resident's photo and personal details.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
