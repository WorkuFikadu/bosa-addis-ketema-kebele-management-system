<?php
// includes/payment_handler.php

function displayPaymentGateway($service_key, $resident_id, $resident_name, $info_only = false) {
    global $pdo;

    // Fetch price
    $stmt = $pdo->prepare("SELECT price_etb, service_name_en FROM service_prices WHERE service_key = ?");
    $stmt->execute([$service_key]);
    $service = $stmt->fetch();

    if (!$service) return;

    $price = $service['price_etb'];
    $service_name = $service['service_name_en'];
    $tx_ref = 'TXN-' . strtoupper(substr($service_key, 0, 3)) . '-' . time() . '-' . rand(100, 999);

    ?>
    <div class="card border-0 shadow-sm mb-4 overflow-hidden" id="paymentSection">
        <div class="card-header bg-dark text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="fas fa-wallet me-2 text-warning"></i><?php echo $info_only ? 'Payment Instructions' : 'Digital Payment Processing'; ?></h6>
                <span class="badge bg-primary px-3">Service Fee: <?php echo number_format($price, 2); ?> ETB</span>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="<?php echo $info_only ? 'col-12' : 'col-md-7'; ?>">
                    <h5 class="mb-1 fw-bold"><?php echo htmlspecialchars($service_name); ?></h5>
                    <p class="text-muted small">Target: <strong id="payment-target-name"><?php echo $resident_name; ?></strong></p>
                    
                    <div class="mb-4">
                        <label class="form-label d-block fw-bold small text-uppercase text-muted">Avalaible Payment Methods</label>
                        <div class="d-flex gap-2 mt-2 flex-wrap">
                            <label class="payment-option border rounded p-2 text-center flex-fill cursor-pointer active" id="label-telebirr">
                                <input type="radio" name="payment_method" value="Telebirr" checked class="d-none">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/e/e0/Telebirr_logo.png" style="height: 25px; object-fit: contain;" class="d-block mx-auto mb-1">
                                <span class="fw-bold" style="font-size: 0.7rem;">Telebirr</span>
                            </label>
                            <label class="payment-option border rounded p-2 text-center flex-fill cursor-pointer" id="label-cbe">
                                <input type="radio" name="payment_method" value="CBE Birr" class="d-none">
                                <img src="https://www.combanketh.et/images/CBE-Birr-Logo.png" style="height: 25px; object-fit: contain;" class="d-block mx-auto mb-1">
                                <span class="fw-bold" style="font-size: 0.7rem;">CBE Birr</span>
                            </label>
                            <label class="payment-option border rounded p-2 text-center flex-fill cursor-pointer" id="label-sinqe">
                                <input type="radio" name="payment_method" value="Sinqe Bank" class="d-none">
                                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR0D6yD96j6D8uQ6R-_f6i2Z2m9x1Fz6F6F6w&s" style="height: 25px; object-fit: contain;" class="d-block mx-auto mb-1" onerror="this.src='https://ui-avatars.com/api/?name=Sinqe+Bank&background=ffcc00&color=000'">
                                <span class="fw-bold" style="font-size: 0.7rem;">Sinqe</span>
                            </label>
                            <label class="payment-option border rounded p-2 text-center flex-fill cursor-pointer" id="label-coop">
                                <input type="radio" name="payment_method" value="Coop Bank" class="d-none">
                                <img src="https://coopbankoromia.com.et/wp-content/uploads/2021/04/coop-logo-2.png" style="height: 25px; object-fit: contain;" class="d-block mx-auto mb-1" onerror="this.src='https://ui-avatars.com/api/?name=Coop+Bank&background=2e7d32&color=fff'">
                                <span class="fw-bold" style="font-size: 0.7rem;">COOP</span>
                            </label>
                            <label class="payment-option border rounded p-2 text-center flex-fill cursor-pointer" id="label-cash">
                                <input type="radio" name="payment_method" value="Cash" class="d-none">
                                <i class="fas fa-money-bill-wave fa-lg d-block mx-auto mb-1 text-success"></i>
                                <span class="fw-bold" style="font-size: 0.7rem;">Cash</span>
                            </label>
                        </div>
                    </div>

                    <div class="payment-instructions bg-light p-3 rounded-3 border-start border-4 border-primary mb-3 shadow-sm">
                        <div id="instr-content">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-primary me-2">STEP 1</span>
                                <span class="fw-bold text-dark small">Dial <span id="payment-code">*127#</span> or use Mobile App</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-primary me-2">STEP 2</span>
                                <span class="fw-bold text-dark small">Send to: <span id="payment-dest" class="text-primary fw-bold">Merchant: 778899</span></span>
                            </div>
                            <div class="d-flex align-items-center mb-0">
                                <span class="badge bg-primary me-2">STEP 3</span>
                                <span class="fw-bold text-dark small">Pay exact amount: <span class="text-success fw-bold"><?php echo $price; ?> ETB</span></span>
                            </div>
                        </div>
                    </div>

                    <?php if ($info_only): ?>
                        <div class="alert alert-warning py-2 mb-0" style="font-size: 0.75rem;">
                            <i class="fas fa-exclamation-triangle me-2"></i><strong>Note:</strong> You can pay now or later. Verification via screenshot will be required during printing.
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!$info_only): ?>
                <div class="col-md-5 text-center border-start p-3 bg-light rounded-end">
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold text-primary mb-1" style="font-size: 0.75rem;">TRANSACTION REFERENCE / ID</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="payment_ref" id="payment_ref_input" value="<?php echo $tx_ref; ?>" class="form-control fw-bold border-primary text-center" placeholder="Reference from SMS">
                            <button class="btn btn-primary" type="button" onclick="navigator.clipboard.writeText('<?php echo $tx_ref; ?>')"><i class="fas fa-copy"></i></button>
                        </div>
                    </div>
                    
                    <div class="mb-3 text-start" id="proof_section">
                        <label class="form-label fw-bold text-danger mb-1" style="font-size: 0.75rem;"><i class="fas fa-camera me-1"></i> UPLOAD PAYMENT SCREENSHOT</label>
                        <div class="input-group input-group-sm">
                            <input type="file" name="payment_proof" id="proof_input" class="form-control border-danger" accept="image/*" required>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.65rem;">Take a screenshot of the successful transaction.</div>
                    </div>

                    <input type="hidden" name="payment_amount" value="<?php echo $price; ?>">
                    <input type="hidden" name="payment_required" value="1">
                    <input type="hidden" name="service_key" value="<?php echo $service_key; ?>">
                    <input type="hidden" id="payment_resident_id" <?php echo ($resident_id > 0) ? 'name="resident_id"' : ''; ?> value="<?php echo $resident_id; ?>">
                    
                    <div class="alert alert-info py-2 px-3 mb-0" style="font-size: 0.7rem;">
                        <i class="fas fa-info-circle me-1"></i> For <strong>Cash</strong> payments, the screenshot is not required.
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        .payment-option { cursor: pointer; transition: all 0.2s; background: #fff; opacity: 0.6; }
        .payment-option.active { border-color: #0d6efd !important; background: #eef6ff; box-shadow: 0 4px 6px rgba(13,110,253,0.1); opacity: 1; transform: translateY(-2px); }
        .payment-option:hover { border-color: #0d6efd; background: #f8fbff; opacity: 1; }
    </style>

    <script id="payment_script_<?php echo rand(1000, 9999); ?>">
    (function() {
        const methods = {
            'Telebirr': { code: '*127#', dest: 'Merchant: 778899', color: '#0d6efd' },
            'CBE Birr': { code: '*847#', dest: 'Acc: 1000123456789', color: '#6c2a8c' },
            'Sinqe Bank': { code: '*969#', dest: 'Acc: 9988776655', color: '#ffcc00' },
            'Coop Bank': { code: '*841#', dest: 'Acc: 4433221100', color: '#2e7d32' },
            'Cash': { code: 'N/A', dest: 'Kebele Cashier Desk', color: '#198754' }
        };

        const container = document.currentScript.parentElement;
        container.querySelectorAll('.payment-option').forEach(opt => {
            opt.addEventListener('click', function() {
                container.querySelectorAll('.payment-option').forEach(el => el.classList.remove('active'));
                this.classList.add('active');
                
                const val = this.querySelector('input').value;
                const data = methods[val];
                
                container.querySelector('#payment-code').textContent = data.code;
                container.querySelector('#payment-dest').textContent = data.dest;
                container.querySelector('#payment-dest').style.color = data.color;
                
                const refInput = container.querySelector('#payment_ref_input');
                const proofInput = container.querySelector('#proof_input');
                
                if(!<?php echo $info_only ? 'true' : 'false'; ?>) {
                    if(refInput && proofInput) {
                        if(val === 'Cash') {
                            refInput.value = 'CASH-' + Math.floor(Math.random()*10000);
                            refInput.readOnly = true;
                            proofInput.required = false;
                            container.querySelector('#proof_section').style.opacity = '0.5';
                        } else {
                            refInput.value = '<?php echo $tx_ref; ?>';
                            refInput.readOnly = false;
                            proofInput.required = true;
                            container.querySelector('#proof_section').style.opacity = '1';
                        }
                    }
                }
            });
        });
    })();
    </script>
    <?php
}

function processPaymentSubmission($pdo, $resident_id, $service_key) {
    if (isset($_POST['payment_required'])) {
        $amount     = $_POST['payment_amount'];
        $method     = $_POST['payment_method'];
        $ref        = $_POST['payment_ref'];
        $status     = ($method === 'Cash') ? 'Completed' : 'Pending';
        $proof_path = null;

        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/payments/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $file_ext = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
            $filename = 'proof_' . time() . '_' . rand(100, 999) . '.' . $file_ext;
            $target_file = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $target_file)) {
                $proof_path = $filename;
            }
        }
        
        $stmt_pay = $pdo->prepare("INSERT INTO transactions (resident_id, service_type, amount, payment_method, transaction_ref, status, payment_proof) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_pay->execute([$resident_id, $service_key, $amount, $method, $ref, $status, $proof_path]);
        return true;
    }
    return false;
}
