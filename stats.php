<?php
// stats.php - Public Statistics Dashboard
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/lang.php';

// 1. Gender Distribution
$genderData = $pdo->query("SELECT s, COUNT(*) as count FROM individuals GROUP BY s")->fetchAll(PDO::FETCH_ASSOC);

// 2. Age Distribution (Groups)
$ageGroups = [
    '0-17' => 0,
    '18-35' => 0,
    '36-60' => 0,
    '60+' => 0
];
$ageData = $pdo->query("SELECT age FROM ages")->fetchAll(PDO::FETCH_COLUMN);
foreach ($ageData as $age) {
    if ($age < 18) $ageGroups['0-17']++;
    else if ($age <= 35) $ageGroups['18-35']++;
    else if ($age <= 60) $ageGroups['36-60']++;
    else $ageGroups['60+']++;
}

// 3. Education Levels
$eduData = $pdo->query("SELECT level_edu, COUNT(*) as count FROM individuals GROUP BY level_edu LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

// 4. Employment Status
$occData = $pdo->query("SELECT occ, COUNT(*) as count FROM individuals GROUP BY occ LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// 5. Aggregate Stats for Counters
$total_residents = $pdo->query("SELECT COUNT(*) FROM individuals")->fetchColumn();
$total_ids = $pdo->query("SELECT COUNT(*) FROM id_cards WHERE status = 'Active'")->fetchColumn();
$total_births = $pdo->query("SELECT COUNT(*) FROM vital_certificates WHERE cert_type = 'birth'")->fetchColumn();
$total_deaths = $pdo->query("SELECT COUNT(*) FROM vital_certificates WHERE cert_type = 'death'")->fetchColumn();
$total_houses = $pdo->query("SELECT COUNT(*) FROM houses")->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('title_stats'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { admin: { primary: '#4f46e5', secondary: '#0ea5e9', dark: '#0f172a' } },
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

    <!-- Header / Navbar (Unified with Home) -->
    <nav class="fixed w-full z-50 glass py-4 px-6 md:px-12 flex justify-between items-center">
        <div class="flex items-center gap-5">
            <div class="flex items-center gap-4 border-r border-white/10 pr-5">
                <img src="assets/img/ethiopia_flag.png" alt="Ethiopia" class="w-8 h-5 rounded-sm shadow-md">
                <img src="assets/img/oromia_flag.png" alt="Oromia" class="w-8 h-5 rounded-sm shadow-md">
            </div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-admin-secondary rounded-lg flex items-center justify-center">
                    <i class="fas fa-landmark text-white text-xl"></i>
                </div>
                <div class="hidden sm:block">
                    <span class="block font-display font-bold text-xl tracking-tight text-white leading-tight">IFA BULA KEBELE, <span class="text-admin-secondary">RIMS</span></span>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em]"><?php echo __('admin_portal'); ?></span>
                </div>
            </div>
        </div>
        <div class="hidden md:flex gap-8 font-medium">
            <a href="index.php" class="hover:text-admin-secondary transition-colors"><?php echo __('home'); ?></a>
            <a href="services.php" class="hover:text-admin-secondary transition-colors"><?php echo __('services'); ?></a>
            <a href="stats.php" class="text-admin-secondary font-bold"><?php echo __('stats'); ?></a>
            <a href="about.php" class="hover:text-admin-secondary transition-colors"><?php echo __('about'); ?></a>
        </div>
        
        <div class="flex items-center gap-6">
            <!-- Language Dropdown -->
            <div class="relative group">
                <button class="flex items-center gap-2 text-white hover:text-admin-secondary transition-colors font-medium">
                    <i class="fas fa-globe"></i> <?php echo strtoupper($current_lang); ?>
                </button>
                <div class="absolute right-0 top-full mt-2 w-40 glass rounded-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-[100] p-1">
                    <a href="?lang=en" class="block px-4 py-2 hover:bg-white/10 rounded-lg text-sm transition-colors">English</a>
                    <a href="?lang=om" class="block px-4 py-2 hover:bg-white/10 rounded-lg text-sm transition-colors">Afaan Oromoo</a>
                    <a href="?lang=am" class="block px-4 py-2 hover:bg-white/10 rounded-lg text-sm transition-colors">አማርኛ</a>
                </div>
            </div>

            <a href="auth/login.php" class="bg-admin-secondary hover:bg-admin-secondary/80 text-white px-6 py-2 rounded-full font-semibold transition-all shadow-lg shadow-admin-secondary/20">
                <?php echo __('staff_portal'); ?> <i class="fas fa-arrow-right ml-2 text-sm"></i>
            </a>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto pt-32 pb-12">
        <!-- Header -->
        <div class="flex justify-between items-center mb-12">
            <div class="max-w-3xl">
                <h1 class="text-4xl md:text-5xl font-display font-bold text-white tracking-tight mb-4"><?php echo __('stats_hero_title_accent'); ?></h1>
                <p class="text-slate-400 text-lg leading-relaxed">
                    <?php echo __('stats_hero_desc'); ?>
                </p>
            </div>
            <div class="hidden md:block">
                <div class="glass px-6 py-3 rounded-2xl flex items-center gap-4">
                    <div class="w-10 h-10 bg-green-500/20 rounded-full flex items-center justify-center text-green-500">
                        <i class="fas fa-sync-alt animate-spin-slow"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-bold tracking-widest"><?php echo __('stats_live_data'); ?></p>
                        <p class="text-sm font-bold text-white"><?php echo __('stats_last_updated'); ?>: <?php echo __('stats_just_now'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Counters -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-12">
            <div class="glass p-6 rounded-2xl border-t-2 border-t-indigo-500 shadow-xl" data-aos="fade-up">
                <div class="flex justify-between items-start mb-2">
                    <i class="fas fa-users text-indigo-400 text-xl"></i>
                </div>
                <h4 class="text-3xl text-white font-display font-bold"><?php echo number_format($total_residents); ?></h4>
                <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mt-1"><?php echo __('total_residents'); ?></p>
            </div>
            <div class="glass p-6 rounded-2xl border-t-2 border-t-emerald-500 shadow-xl" data-aos="fade-up" data-aos-delay="50">
                <div class="flex justify-between items-start mb-2">
                    <i class="fas fa-id-card text-emerald-400 text-xl"></i>
                </div>
                <h4 class="text-3xl text-white font-display font-bold"><?php echo number_format($total_ids); ?></h4>
                <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mt-1"><?php echo __('active_ids'); ?></p>
            </div>
            <div class="glass p-6 rounded-2xl border-t-2 border-t-amber-500 shadow-xl" data-aos="fade-up" data-aos-delay="100">
                <div class="flex justify-between items-start mb-2">
                    <i class="fas fa-baby text-amber-400 text-xl"></i>
                </div>
                <h4 class="text-3xl text-white font-display font-bold"><?php echo number_format($total_births); ?></h4>
                <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mt-1"><?php echo __('birth_records'); ?></p>
            </div>
            <div class="glass p-6 rounded-2xl border-t-2 border-t-rose-500 shadow-xl" data-aos="fade-up" data-aos-delay="150">
                <div class="flex justify-between items-start mb-2">
                    <i class="fas fa-dove text-rose-400 text-xl"></i>
                </div>
                <h4 class="text-3xl text-white font-display font-bold"><?php echo number_format($total_deaths); ?></h4>
                <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mt-1"><?php echo __('death_records'); ?></p>
            </div>
            <div class="glass p-6 rounded-2xl border-t-2 border-t-blue-500 shadow-xl" data-aos="fade-up" data-aos-delay="200">
                <div class="flex justify-between items-start mb-2">
                    <i class="fas fa-house-chimney text-blue-400 text-xl"></i>
                </div>
                <h4 class="text-3xl text-white font-display font-bold"><?php echo number_format($total_houses); ?></h4>
                <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mt-1"><?php echo __('total_houses'); ?></p>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <!-- Gender Chart -->
            <div class="glass p-6 rounded-3xl h-[350px] flex flex-col items-center">
                <h3 class="text-lg font-bold mb-6 self-start"><i class="fas fa-venus-mars mr-2 text-pink-500"></i> <?php echo __('stats_gender_dist'); ?></h3>
                <div class="w-full flex-grow">
                    <canvas id="genderChart"></canvas>
                </div>
            </div>

            <!-- Age Groups -->
            <div class="glass p-6 rounded-3xl h-[350px] flex flex-col items-center">
                <h3 class="text-lg font-bold mb-6 self-start"><i class="fas fa-baby-carriage mr-2 text-indigo-500"></i> <?php echo __('stats_age_demographics'); ?></h3>
                <div class="w-full flex-grow">
                    <canvas id="ageChart"></canvas>
                </div>
            </div>

            <!-- Education -->
            <div class="glass p-6 rounded-3xl h-[350px] flex flex-col items-center lg:col-span-2">
                <h3 class="text-lg font-bold mb-2 self-start"><i class="fas fa-user-graduate mr-2 text-amber-500"></i> <?php echo __('stats_edu_attainment'); ?></h3>
                <div class="w-full flex-grow">
                    <canvas id="eduChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            <!-- Employment -->
            <div class="glass p-8 rounded-3xl md:col-span-2">
                <h3 class="text-xl font-bold mb-8"><i class="fas fa-briefcase mr-2 text-emerald-500"></i> <?php echo __('stats_top_occupations'); ?></h3>
                <div class="w-full h-[300px]">
                    <canvas id="occChart"></canvas>
                </div>
            </div>
            
            <!-- Summary Card -->
            <div class="bg-gradient-to-br from-admin-primary to-admin-secondary p-8 rounded-3xl text-white flex flex-col justify-between">
                <div>
                    <h3 class="text-2xl font-bold mb-4"><?php echo __('stats_gov_overview'); ?></h3>
                    <p class="text-blue-100/90 leading-relaxed text-sm">
                        <?php echo __('stats_gov_desc'); ?>
                    </p>
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-white/10">
                        <span class="text-blue-100/60 uppercase text-xs tracking-widest font-bold"><?php echo __('stats_data_accuracy'); ?></span>
                        <span class="font-bold">98.4%</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-white/10">
                        <span class="text-blue-100/60 uppercase text-xs tracking-widest font-bold"><?php echo __('stats_digital_adoption'); ?></span>
                        <span class="font-bold">72%</span>
                    </div>
                    <button class="w-full bg-white text-slate-900 font-bold py-3 rounded-xl mt-4 hover:bg-slate-100 transition-colors shadow-lg hover:shadow-xl">
                        <?php echo __('stats_download_report'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Gender Chart
        new Chart(document.getElementById('genderChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($genderData, 's')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($genderData, 'count')); ?>,
                    backgroundColor: ['#0ea5e9', '#ec4899', '#f59e0b'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { color: '#94a3b8', font: { size: 12 } } } }
            }
        });

        // Age Chart
        new Chart(document.getElementById('ageChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($ageGroups)); ?>,
                datasets: [{
                    label: 'Residents',
                    data: <?php echo json_encode(array_values($ageGroups)); ?>,
                    backgroundColor: '#6366f1',
                    borderRadius: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#64748b' } },
                    x: { grid: { display: false }, ticks: { color: '#64748b' } }
                }
            }
        });

        // Education Chart
        new Chart(document.getElementById('eduChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($eduData, 'level_edu')); ?>,
                datasets: [{
                    label: 'Count',
                    data: <?php echo json_encode(array_column($eduData, 'count')); ?>,
                    backgroundColor: '#f59e0b',
                    borderRadius: 8
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { grid: { display: false }, ticks: { color: '#64748b' } },
                    x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#64748b' } }
                }
            }
        });

        // Occupation Chart
        new Chart(document.getElementById('occChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($occData, 'occ')); ?>,
                datasets: [{
                    label: 'Residents',
                    data: <?php echo json_encode(array_column($occData, 'count')); ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 5,
                    pointBackgroundColor: '#10b981'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#64748b' } },
                    x: { grid: { display: false }, ticks: { color: '#64748b' } }
                }
            }
        });
    </script>
    <footer class="bg-admin-dark border-t border-white/5 pt-16 pb-8 mt-24">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-12 mb-12">
                <div class="space-y-6">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-admin-secondary rounded flex items-center justify-center">
                            <i class="fas fa-landmark text-white"></i>
                        </div>
                        <span class="font-display font-bold text-lg text-white">IFA BULA KEBELE, RIMS</span>
                    </div>
                        © 2026 Developed by Worku Fikadu
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
                        <i class="fas fa-envelope text-admin-secondary mr-2 text-xs"></i> workufikadu643@gmail.com
                    </p>
                </div>
            </div>
            <div class="border-t border-white/5 pt-10 mt-10">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="text-slate-500 text-xs font-medium">
                        &copy; 2026 <span class="text-white tracking-widest uppercase">Ifa Bula Kebele Administration</span>. All Rights Reserved.
                    </div>
                    <div class="flex flex-wrap justify-center gap-6 text-[10px] font-bold tracking-widest uppercase">
                        <div class="flex items-center gap-2 text-slate-400">
                            <i class="fas fa-code text-admin-secondary"></i>
                            <span>Developed by <span class="text-white">Worku Fikadu</span></span>
                        </div>
                        <a href="mailto:workufikadu643@gmail.com" class="flex items-center gap-2 text-slate-400 hover:text-admin-secondary transition-all">
                            <i class="fas fa-envelope"></i>
                            <span>workufikadu643@gmail.com</span>
                        </a>
                        <a href="tel:+251934953593" class="flex items-center gap-3 text-slate-400 hover:text-admin-secondary transition-all">
                            <i class="fas fa-phone"></i>
                            <span>+251 934 953 593</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
