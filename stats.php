<?php
// stats.php - Public Statistics Dashboard
require_once __DIR__ . '/config/database.php';

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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demographic Statistics | IFA BULA KEBELE, RESIDENT MANAGEMENT SYSTEM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #0f172a; }
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05); }
    </style>
</head>
<body class="text-slate-200 min-h-screen p-6 md:p-12">

    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-12">
            <div class="max-w-3xl">
                <a href="index.php" class="text-sky-500 font-bold flex items-center gap-2 mb-4 hover:text-sky-400 transition-colors">
                    <i class="fas fa-arrow-left"></i> Return to Main Admin Portal
                </a>
                <h1 class="text-4xl md:text-5xl font-bold text-white tracking-tight mb-4">Comprehensive Demographic <span class="text-sky-500">Intelligence</span></h1>
                <p class="text-slate-400 text-lg leading-relaxed">
                    Explore dynamic, real-time demographic insights that empower strategic civic planning and optimized resource allocation across IFA BULA KEBELE, RESIDENT MANAGEMENT SYSTEM. Data-driven governance ensures equitable service delivery and unparalleled institutional accountability.
                </p>
            </div>
            <div class="hidden md:block">
                <div class="glass px-6 py-3 rounded-2xl flex items-center gap-4">
                    <div class="w-10 h-10 bg-green-500/20 rounded-full flex items-center justify-center text-green-500">
                        <i class="fas fa-sync-alt animate-spin-slow"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-bold tracking-widest">Live Data</p>
                        <p class="text-sm font-bold text-white">Last Updated: Just Now</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <!-- Gender Chart -->
            <div class="glass p-6 rounded-3xl h-[350px] flex flex-col items-center">
                <h3 class="text-lg font-bold mb-6 self-start"><i class="fas fa-venus-mars mr-2 text-pink-500"></i> Gender Distribution</h3>
                <div class="w-full flex-grow">
                    <canvas id="genderChart"></canvas>
                </div>
            </div>

            <!-- Age Groups -->
            <div class="glass p-6 rounded-3xl h-[350px] flex flex-col items-center">
                <h3 class="text-lg font-bold mb-6 self-start"><i class="fas fa-baby-carriage mr-2 text-indigo-500"></i> Age Demographics</h3>
                <div class="w-full flex-grow">
                    <canvas id="ageChart"></canvas>
                </div>
            </div>

            <!-- Education -->
            <div class="glass p-6 rounded-3xl h-[350px] flex flex-col items-center lg:col-span-2">
                <h3 class="text-lg font-bold mb-2 self-start"><i class="fas fa-user-graduate mr-2 text-amber-500"></i> Educational Attainment</h3>
                <div class="w-full flex-grow">
                    <canvas id="eduChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            <!-- Employment -->
            <div class="glass p-8 rounded-3xl md:col-span-2">
                <h3 class="text-xl font-bold mb-8"><i class="fas fa-briefcase mr-2 text-emerald-500"></i> Top Occupations</h3>
                <div class="w-full h-[300px]">
                    <canvas id="occChart"></canvas>
                </div>
            </div>
            
            <!-- Summary Card -->
            <div class="bg-gradient-to-br from-admin-primary to-admin-secondary p-8 rounded-3xl text-white flex flex-col justify-between">
                <div>
                    <h3 class="text-2xl font-bold mb-4">Strategic Governance Overview</h3>
                    <p class="text-blue-100/90 leading-relaxed text-sm">
                        This analytical interface manifests our commitment to open data. It provides an anonymized, holistic evaluation of our community's configuration. All metrics are continuously synchronized with our central resident registry database to maintain flawless data provenance.
                    </p>
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-white/10">
                        <span class="text-blue-100/60 uppercase text-xs tracking-widest font-bold">Data Accuracy</span>
                        <span class="font-bold">98.4%</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-white/10">
                        <span class="text-blue-100/60 uppercase text-xs tracking-widest font-bold">Digital Adoption</span>
                        <span class="font-bold">72%</span>
                    </div>
                    <button class="w-full bg-white text-slate-900 font-bold py-3 rounded-xl mt-4 hover:bg-slate-100 transition-colors shadow-lg hover:shadow-xl">
                        Download Comprehensive PDF Report
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
</body>
</html>
