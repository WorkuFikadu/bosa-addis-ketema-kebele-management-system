<?php
// modules/idcards/index.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$query = "SELECT ic.*, i.fname, i.lname, i.phot, ag.age, i.occ, i.nat
          FROM id_cards ic
          JOIN individuals i ON ic.resident_id = i.id
          LEFT JOIN ages ag ON i.id = ag.id";
$id_cards = $pdo->query($query)->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?php echo __('id_card_mgmt'); ?></h2>
    <?php if ($_SESSION['role'] !== 'security'): ?>
    <a href="generate.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i><?php echo __('issue_new_id'); ?>
    </a>
    <?php endif; ?>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th><?php echo __('id_number_label'); ?></th>
                    <th><?php echo __('full_name'); ?></th>
                    <th><?php echo __('issue_date'); ?></th>
                    <th><?php echo __('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($id_cards as $card): ?>
                <tr>
                    <td><strong><?php echo $card['id_num']; ?></strong></td>
                    <td><?php echo "{$card['fname']} {$card['lname']}"; ?></td>
                    <td><?php echo date('M d, Y', strtotime($card['issue_date'])); ?></td>
                    <td>
                        <?php if ($_SESSION['role'] !== 'security'): ?>
                            <a href="print.php?id=<?php echo $card['id']; ?>" class="btn btn-sm btn-outline-success" target="_blank" title="Print ID">
                                <i class="fas fa-print me-1"></i> Print
                            </a>
                            
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <a href="delete.php?id=<?php echo $card['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Revoke this ID card?')" title="<?php echo __('delete'); ?>">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted small italic"><?php echo __('view_details'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($id_cards)): ?>
                <tr><td colspan="4" class="text-center py-4"><?php echo __('no_records'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
