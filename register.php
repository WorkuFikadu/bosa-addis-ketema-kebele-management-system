<?php
// register.php - Public Registration Portal
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/lang.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo->beginTransaction();
        
        $fname = $_POST['fname'] ?? '';
        $mname = $_POST['mname'] ?? '';
        $lname = $_POST['lname'] ?? '';
        $s = $_POST['sex'] ?? 'Male';
        $bdate = $_POST['bdate'] ?? date('Y-m-d');
        $mar = $_POST['mar'] ?? 'Single';
        $nat = $_POST['nat'] ?? 'Ethiopian';
        $level_edu = $_POST['level_edu'] ?? 'None';
        $relg = $_POST['relg'] ?? 'Other';
        $occ = $_POST['occ'] ?? 'Unemployed';
        
        // Address info
        $region = $_POST['region'] ?? 'Oromia';
        $zone = $_POST['zone'] ?? 'Jimma';
        $city = $_POST['city'] ?? 'Jimma';
        $kebele = $_POST['kebele'] ?? 'Bosa Addis';
        $pho_no = $_POST['pho_no'] ?? '';
        $email = $_POST['email'] ?? '';
        
        $stmt = $pdo->prepare("INSERT INTO individuals (fname, mname, lname, mar, s, nat, level_edu, relg, occ, phot) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'default_profile.png')");
        $stmt->execute([$fname, $mname, $lname, $mar, $s, $nat, $level_edu, $relg, $occ]);
        $ind_id = $pdo->lastInsertId();
        
        // Calculate age
        $birthDate = new DateTime($bdate);
        $today = new DateTime('today');
        $age = $birthDate->diff($today)->y;
        
        $stmt2 = $pdo->prepare("INSERT INTO ages (id, bdate, age) VALUES (?, ?, ?)");
        $stmt2->execute([$ind_id, $bdate, $age]);
        
        $stmt3 = $pdo->prepare("INSERT INTO addresses (id, region, zone, city, kebele, pho_no, email) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt3->execute([$ind_id, $region, $zone, $city, $kebele, $pho_no, $email]);
        
        $pdo->commit();
        $success_msg = __("reg_success_title") . " (ID: #{$ind_id}) " . __("visit_office_finalize");
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = __("reg_failed_title") . " " . $e->getMessage();
    }
}

// Ensure visit_office_finalize key is handled or used directly
if (!isset($translations[$current_lang]['visit_office_finalize'])) {
    $visit_office_msg = "Please visit the Kebele office with your supporting documents within 14 days to finalize your ID Generation.";
} else {
    $visit_office_msg = __("visit_office_finalize");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('nav_register'); ?> | BOSA ADDIS KEBELE MANAGEMENT SYSTEM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { admin: { primary: '#4f46e5', secondary: '#0ea5e9', dark: '#2e1065', card: '#4c1d95' } },
                    fontFamily: { sans: ['Inter', 'sans-serif'], display: ['Outfit', 'sans-serif'], }
                }
            }
        }
    </script>
    <style>
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
    </style>
</head>
<body class="bg-admin-dark text-slate-200 font-sans selection:bg-admin-secondary selection:text-white">

    <!-- Unified Navbar -->
    <?php include __DIR__ . '/includes/public_navbar.php'; ?>

    <main class="pt-40 pb-24 min-h-screen">
        <div class="container mx-auto px-6 max-w-4xl">
            <div class="text-center mb-12" data-aos="fade-up">
                <h2 class="text-admin-secondary font-bold uppercase tracking-[0.3em] text-sm mb-4"><?php echo __('resident_portal'); ?></h2>
                <h1 class="text-4xl md:text-5xl font-display font-extrabold text-white mb-6"><?php echo __('digital_enrollment'); ?></h1>
                <p class="text-slate-400 max-w-2xl mx-auto leading-relaxed">
                    <?php echo __('reg_desc'); ?>
                </p>
            </div>

            <div class="glass p-8 md:p-12 rounded-3xl border border-white/10 shadow-2xl relative overflow-hidden" data-aos="fade-up" data-aos-delay="100">
                <!-- Abstract Glow -->
                <div class="absolute -top-32 -right-32 w-64 h-64 bg-admin-primary/20 blur-[100px] rounded-full pointer-events-none"></div>

                <?php if($success_msg): ?>
                    <div class="mb-8 p-6 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl flex items-start gap-4">
                        <i class="fas fa-check-circle text-2xl mt-1"></i>
                        <div>
                            <h4 class="font-bold text-lg mb-1"><?php echo __('reg_success_title'); ?></h4>
                            <p class="leading-relaxed"><?php echo htmlspecialchars($success_msg); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($error_msg): ?>
                    <div class="mb-8 p-6 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl flex items-start gap-4">
                        <i class="fas fa-exclamation-triangle text-2xl mt-1"></i>
                        <div>
                            <h4 class="font-bold text-lg mb-1"><?php echo __('reg_failed_title'); ?></h4>
                            <p class="leading-relaxed"><?php echo htmlspecialchars($error_msg); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST" class="space-y-8 relative z-10">
                    
                    <!-- Section 1: Personal -->
                    <div class="space-y-6">
                        <h3 class="text-xl font-bold text-white flex items-center gap-3 border-b border-white/10 pb-4">
                            <i class="fas fa-user text-admin-secondary"></i> <?php echo __('personal_info'); ?>
                        </h3>
                        <div class="grid md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="text-slate-400 text-xs font-bold uppercase tracking-wider"><?php echo __('first_name'); ?></label>
                                <input type="text" name="fname" required class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-secondary transition-colors">
                            </div>
                            <div class="space-y-2">
                                <label class="text-slate-400 text-xs font-bold uppercase tracking-wider"><?php echo __('mname_label'); ?></label>
                                <input type="text" name="mname" required class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-secondary transition-colors">
                            </div>
                            <div class="space-y-2">
                                <label class="text-slate-400 text-xs font-bold uppercase tracking-wider"><?php echo __('lname_label'); ?></label>
                                <input type="text" name="lname" required class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-secondary transition-colors">
                            </div>
                        </div>

                        <div class="grid md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="text-slate-400 text-xs font-bold uppercase tracking-wider"><?php echo __('sex'); ?></label>
                                <select name="sex" class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-secondary transition-colors">
                                    <option value="Male"><?php echo __('male'); ?></option>
                                    <option value="Female"><?php echo __('female'); ?></option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-slate-400 text-xs font-bold uppercase tracking-wider"><?php echo __('birth_date'); ?></label>
                                <input type="date" name="bdate" required class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-secondary transition-colors [color-scheme:dark]">
                            </div>
                            <div class="space-y-2">
                                <label class="text-slate-400 text-xs font-bold uppercase tracking-wider"><?php echo __('marital_status'); ?></label>
                                <select name="mar" class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-secondary transition-colors">
                                    <option value="Single"><?php echo __('single'); ?></option>
                                    <option value="Married"><?php echo __('married'); ?></option>
                                    <option value="Divorced"><?php echo __('divorced'); ?></option>
                                    <option value="Widowed"><?php echo __('widowed'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Demographics -->
                    <div class="space-y-6">
                        <h3 class="text-xl font-bold text-white flex items-center gap-3 border-b border-white/10 pb-4">
                            <i class="fas fa-graduation-cap text-admin-primary"></i> <?php echo __('demographic_details'); ?>
                        </h3>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-slate-400 text-xs font-bold uppercase tracking-wider"><?php echo __('occupation'); ?></label>
                                <input type="text" name="occ" required placeholder="..." class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-secondary transition-colors">
                            </div>
                            <div class="space-y-2">
                                <label class="text-slate-400 text-xs font-bold uppercase tracking-wider"><?php echo __('edu_level_label'); ?></label>
                                <select name="level_edu" class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-secondary transition-colors">
                                    <option value="None">None</option>
                                    <option value="Primary">Primary School (1-8)</option>
                                    <option value="Secondary">Secondary School (9-12)</option>
                                    <option value="Diploma">Diploma / TVET</option>
                                    <option value="Bachelor">Bachelor's Degree</option>
                                    <option value="Masters/PhD">Master's / PhD</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-slate-400 text-xs font-bold uppercase tracking-wider"><?php echo __('religion'); ?></label>
                                <select name="relg" class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-secondary transition-colors">
                                    <option value="Orthodox">Orthodox Christianity</option>
                                    <option value="Muslim">Islam</option>
                                    <option value="Protestant">Protestant</option>
                                    <option value="Catholic">Catholic</option>
                                    <option value="Waaqeffannaa">Waaqeffannaa</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-slate-400 text-xs font-bold uppercase tracking-wider"><?php echo __('nationality'); ?></label>
                                <input type="text" name="nat" value="Ethiopian" class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-secondary transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Contact & Address -->
                    <div class="space-y-6">
                        <h3 class="text-xl font-bold text-white flex items-center gap-3 border-b border-white/10 pb-4">
                            <i class="fas fa-map-marked-alt text-emerald-500"></i> <?php echo __('local_kebele_addr'); ?>
                        </h3>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-slate-400 text-xs font-bold uppercase tracking-wider"><?php echo __('phone_no'); ?></label>
                                <input type="tel" name="pho_no" required placeholder="09XX..." class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-secondary transition-colors">
                            </div>
                            <div class="space-y-2">
                                <label class="text-slate-400 text-xs font-bold uppercase tracking-wider"><?php echo __('email_addr'); ?> (<?php echo __('optional'); ?>)</label>
                                <input type="email" name="email" class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-secondary transition-colors">
                            </div>
                            <div class="space-y-2">
                                <label class="text-slate-400 text-xs font-bold uppercase tracking-wider"><?php echo __('region'); ?></label>
                                <input type="text" name="region" value="Oromia" readonly class="w-full bg-slate-900/50 border border-white/5 text-slate-400 rounded-xl px-4 py-3 outline-none cursor-not-allowed">
                            </div>
                             <div class="space-y-2">
                                <label class="text-slate-400 text-xs font-bold uppercase tracking-wider"><?php echo __('zone'); ?> / <?php echo __('city'); ?></label>
                                <input type="text" name="zone" value="Jimma" readonly class="w-full bg-slate-900/50 border border-white/5 text-slate-400 rounded-xl px-4 py-3 outline-none cursor-not-allowed">
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" class="w-full md:w-auto bg-admin-primary hover:bg-indigo-500 text-white px-10 py-4 rounded-xl font-bold transition-all shadow-[0_0_20px_rgba(79,70,229,0.4)] hover:shadow-[0_0_30px_rgba(79,70,229,0.6)] text-lg flex items-center justify-center gap-3">
                            <i class="fas fa-file-signature text-xl"></i> <?php echo __('submit_app'); ?>
                        </button>
                        <p class="text-xs text-slate-500 mt-4 text-center md:text-left">
                            <i class="fas fa-shield-alt mr-1"></i> <?php echo __('data_privacy_notice'); ?>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer class="bg-admin-dark border-t border-white/5 pt-16 pb-8 mt-24">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-12 mb-12">
                <div class="space-y-6">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-admin-secondary rounded flex items-center justify-center">
                            <i class="fas fa-landmark text-white"></i>
                        </div>
                        <span class="font-display font-bold text-lg text-white"><?php echo __('kms_title'); ?></span>
                    </div>
                    <p class="text-slate-500 text-sm"><?php echo __('footer_tagline'); ?></p>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6 uppercase tracking-widest text-xs"><?php echo __('quick_links'); ?></h4>
                    <ul class="space-y-4 text-slate-500 text-sm">
                        <li><a href="#" class="hover:text-admin-secondary transition-colors"><?php echo __('privacy_policy'); ?></a></li>
                        <li><a href="#" class="hover:text-admin-secondary transition-colors"><?php echo __('tos'); ?></a></li>
                        <li><a href="#" class="hover:text-admin-secondary transition-colors"><?php echo __('official_docs'); ?></a></li>
                        <li><a href="#" class="hover:text-admin-secondary transition-colors"><?php echo __('faq'); ?></a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6 uppercase tracking-widest text-xs"><?php echo __('office_hours'); ?></h4>
                    <ul class="space-y-4 text-slate-500 text-sm">
                        <li><?php echo __('mon_fri'); ?>: 2:30 AM - 11:30 PM</li>
                        <li><?php echo __('saturday'); ?>: 3:30 AM - 10:30 PM</li>
                        <li><?php echo __('sunday'); ?>: <?php echo __('closed'); ?></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6 uppercase tracking-widest text-xs"><?php echo __('contact_us'); ?></h4>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        <i class="fas fa-map-marker-alt text-admin-secondary mr-2"></i> <?php echo __('jimma_city'); ?><br>
                        <i class="fas fa-phone text-admin-secondary mr-2 text-xs"></i> +251 934 953 593<br>
                        <i class="fas fa-envelope text-admin-secondary mr-2 text-xs"></i> support@kebele.gov.et
                    </p>
                </div>
            </div>
            <div class="border-t border-white/5 pt-10 mt-10">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="text-slate-500 text-xs font-medium">
                        &copy; 2026 <span class="text-white tracking-widest uppercase"><?php echo __('kebele_administration'); ?></span>. <?php echo __('all_rights_reserved'); ?>
                    </div>
                    <div class="flex flex-wrap justify-center gap-6 text-[10px] font-bold tracking-widest uppercase">
                        <a href="mailto:support@kebele.gov.et" class="flex items-center gap-2 text-slate-400 hover:text-admin-secondary transition-all">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo __('official_support'); ?></span>
                        </a>
                    </div>
                </div>
                
                <!-- PUBLIC DEVELOPER FOOTER -->
                <div class="mt-8 pt-6 border-t border-white/5 flex flex-col md:flex-row justify-center items-center gap-3 md:gap-6 text-xs text-slate-500 tracking-wider">
                    <span class="uppercase">Developed by: <span class="text-admin-secondary font-bold">WORKU FIKADU</span></span>
                    <span class="hidden md:inline opacity-30">|</span>
                    <span class="flex items-center gap-2"><i class="fas fa-phone text-admin-secondary"></i> +251 934 953 593</span>
                    <span class="hidden md:inline opacity-30">|</span>
                    <span class="flex items-center gap-2 lowercase"><i class="fas fa-envelope text-admin-secondary"></i> workufikadu643@gmail.com</span>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 1000, once: true, offset: 50 });
    </script>
</body>
</html>
