<?php
// index.php - Premium Landing Page
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/lang.php';

// Fetch stats for the landing page
$resident_count = $pdo->query("SELECT COUNT(*) FROM individuals")->fetchColumn();
$house_count = $pdo->query("SELECT COUNT(*) FROM houses")->fetchColumn();
$family_count = $pdo->query("SELECT COUNT(*) FROM families")->fetchColumn();

// Default values if empty
$resident_count = $resident_count ?: 1250;
$house_count = $house_count ?: 450;
$family_count = $family_count ?: 400;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | IFA BULA KEBELE, RESIDENT MANAGEMENT SYSTEM</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Google Fonts: Inter & Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        admin: {
                            primary: '#4f46e5',    // Indigo
                            secondary: '#0ea5e9',  // Sky
                            dark: '#0f172a',       // Slate 900
                            card: '#1e293b'        // Slate 800
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .hero-gradient {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(30, 58, 138, 0.8) 100%);
        }
    </style>
</head>
<body class="bg-admin-dark text-slate-200 font-sans selection:bg-admin-secondary selection:text-white">

    <!-- Header / Navbar -->
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
                    <span class="block font-display font-bold text-xl tracking-tight text-white leading-tight">IFA BULA KEBELE, <span class="text-admin-secondary">RESIDENT MANAGEMENT SYSTEM</span></span>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em]"><?php echo __('admin_portal'); ?></span>
                </div>
            </div>
        </div>
        <div class="hidden md:flex gap-8 font-medium">
            <a href="#" class="hover:text-admin-secondary transition-colors"><?php echo __('home'); ?></a>
            <a href="#services" class="hover:text-admin-secondary transition-colors"><?php echo __('services'); ?></a>
            <a href="stats.php" class="hover:text-admin-secondary transition-colors"><?php echo __('stats'); ?></a>
            <a href="#about" class="hover:text-admin-secondary transition-colors"><?php echo __('about'); ?></a>
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

    <!-- Hero Section -->
    <section class="pt-32 pb-16 bg-admin-dark hero-gradient min-h-screen">
        <!-- Top Video Container -->
        <div class="w-[95%] max-w-[1400px] mx-auto mb-16 rounded-3xl overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.5)] border border-white/10 relative z-10" data-aos="zoom-in">
            <video autoplay muted loop playsinline class="w-full h-[70vh] object-cover block">
                <source src="assets/video/Jimma Zone in Transformation_720p.mp4" type="video/mp4">
            </video>
        </div>
        
        <div class="container mx-auto px-6 relative z-10 flex flex-col items-center text-center">
            <!-- Hero Content -->
            <div class="max-w-4xl mx-auto space-y-8" data-aos="fade-up">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border-white/10 text-admin-secondary text-sm font-bold uppercase tracking-widest mb-4">
                    <span class="w-2 h-2 rounded-full bg-admin-secondary animate-pulse"></span>
                    <?php echo __('system_online'); ?>
                </div>
                <h1 class="text-5xl md:text-7xl font-display font-extrabold text-white leading-tight tracking-tight drop-shadow-lg">
                    <?php echo __('hero_title'); ?>
                </h1>
                <p class="text-xl md:text-2xl text-slate-300 leading-relaxed max-w-3xl mx-auto font-light drop-shadow-md">
                    <?php echo __('hero_desc'); ?>
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-5 pt-8">
                    <a href="#services" class="bg-admin-primary hover:bg-indigo-500 text-white px-8 py-4 rounded-full font-bold transition-all shadow-[0_0_20px_rgba(79,70,229,0.4)] hover:shadow-[0_0_30px_rgba(79,70,229,0.6)] w-full sm:w-auto text-lg hover:-translate-y-1">
                        <?php echo __('our_services'); ?>
                    </a>
                    <a href="#about" class="glass hover:bg-white/10 text-white px-8 py-4 rounded-full font-bold transition-all border border-white/20 hover:border-white/40 w-full sm:w-auto text-lg hover:-translate-y-1">
                        <?php echo __('learn_more'); ?>
                    </a>
                </div>
            </div>

            <!-- Hero Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8 mt-20 w-full max-w-5xl mx-auto" data-aos="fade-up" data-aos-delay="200">
                <div class="glass p-6 rounded-2xl text-center border-t-2 border-t-admin-secondary hover:-translate-y-2 transition-transform duration-300 shadow-xl">
                    <h4 class="text-4xl text-white font-display font-bold mb-2"><?php echo number_format($resident_count); ?>+</h4>
                    <p class="text-slate-400 text-sm font-medium uppercase tracking-wider"><?php echo __('total_residents'); ?></p>
                </div>
                <div class="glass p-6 rounded-2xl text-center border-t-2 border-t-admin-primary hover:-translate-y-2 transition-transform duration-300 shadow-xl">
                    <h4 class="text-4xl text-white font-display font-bold mb-2"><?php echo number_format($house_count); ?>+</h4>
                    <p class="text-slate-400 text-sm font-medium uppercase tracking-wider"><?php echo __('total_houses'); ?></p>
                </div>
                <div class="glass p-6 rounded-2xl text-center border-t-2 border-t-emerald-500 hover:-translate-y-2 transition-transform duration-300 shadow-xl">
                    <h4 class="text-4xl text-white font-display font-bold mb-2"><?php echo number_format($family_count); ?>+</h4>
                    <p class="text-slate-400 text-sm font-medium uppercase tracking-wider"><?php echo __('total_families'); ?></p>
                </div>
                <div class="glass p-6 rounded-2xl text-center border-t-2 border-t-pink-500 hover:-translate-y-2 transition-transform duration-300 shadow-xl">
                    <h4 class="text-4xl text-white font-display font-bold mb-2">100%</h4>
                    <p class="text-slate-400 text-sm font-medium uppercase tracking-wider">Digitized</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-24 bg-slate-900/50">
        <div class="container mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16 space-y-4">
                <h2 class="text-admin-secondary font-bold uppercase tracking-widest text-sm"><?php echo __('core_services'); ?></h2>
                <p class="text-4xl font-display font-bold text-white"><?php echo __('digital_govt'); ?></p>
                <div class="h-1 w-20 bg-admin-secondary mx-auto rounded-full"></div>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Service 1 -->
                <div class="p-8 rounded-2xl bg-admin-dark border border-white/5 hover:border-admin-secondary/50 transition-all group shadow-xl" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-14 h-14 rounded-2xl bg-admin-secondary/10 flex items-center justify-center text-admin-secondary text-2xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3"><?php echo __('service_id_title'); ?></h3>
                    <p class="text-slate-400 text-sm leading-relaxed mb-6"><?php echo __('service_id_desc'); ?></p>
                    <a href="#" class="text-admin-secondary font-bold text-sm flex items-center gap-2 group-hover:gap-4 transition-all">
                        <?php echo __('learn_more'); ?> <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Service 2 -->
                <div class="p-8 rounded-2xl bg-admin-dark border border-white/5 hover:border-admin-secondary/50 transition-all group shadow-xl" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-14 h-14 rounded-2xl bg-admin-secondary/10 flex items-center justify-center text-admin-secondary text-2xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3"><?php echo __('service_civil_title'); ?></h3>
                    <p class="text-slate-400 text-sm leading-relaxed mb-6"><?php echo __('service_civil_desc'); ?></p>
                    <a href="#" class="text-admin-secondary font-bold text-sm flex items-center gap-2 group-hover:gap-4 transition-all">
                        <?php echo __('learn_more'); ?> <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Service 3 -->
                <div class="p-8 rounded-2xl bg-admin-dark border border-white/5 hover:border-admin-secondary/50 transition-all group shadow-xl" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-14 h-14 rounded-2xl bg-admin-secondary/10 flex items-center justify-center text-admin-secondary text-2xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3"><?php echo __('service_clearance_title'); ?></h3>
                    <p class="text-slate-400 text-sm leading-relaxed mb-6"><?php echo __('service_clearance_desc'); ?></p>
                    <a href="#" class="text-admin-secondary font-bold text-sm flex items-center gap-2 group-hover:gap-4 transition-all">
                        <?php echo __('learn_more'); ?> <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="py-24 overflow-hidden">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-2 gap-16 items-center">
                <div class="relative" data-aos="zoom-in">
                    <div class="absolute -top-8 -left-8 w-32 h-32 bg-admin-secondary/10 rounded-full blur-3xl"></div>
                    <img src="assets/img/jimma_about.png" alt="Jimma Town View" class="rounded-3xl shadow-2xl relative z-10 border border-white/10 hover:scale-[1.02] transition-transform duration-500">
                </div>
                <div class="space-y-6" data-aos="fade-left">
                    <h2 class="text-admin-secondary font-bold uppercase tracking-widest text-sm"><?php echo __('office_msg'); ?></h2>
                    <h3 class="text-4xl font-display font-bold text-white leading-tight"><?php echo __('digital_future'); ?></h3>
                    <p class="text-slate-400 leading-relaxed text-lg">
                        <?php echo __('quote_text'); ?>
                    </p>
                    <div class="flex items-center gap-4 pt-4">
                        <div class="w-12 h-12 rounded-full border border-admin-secondary flex items-center justify-center text-admin-secondary">
                            <i class="fas fa-quote-left"></i>
                        </div>
                        <p class="italic text-slate-500"><?php echo __('quote_sub'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-admin-dark border-t border-white/5 pt-16 pb-8">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-12 mb-12">
                <div class="space-y-6">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-admin-secondary rounded flex items-center justify-center">
                            <i class="fas fa-landmark text-white"></i>
                        </div>
                        <span class="font-display font-bold text-lg text-white">IFA BULA KEBELE, RESIDENT MANAGEMENT SYSTEM</span>
                    </div>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        <?php echo __('footer_text'); ?>
                    </p>
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
                        <li>Mon - Fri: 2:30 AM - 11:30 PM</li>
                        <li>Saturday: 3:30 AM - 10:30 PM</li>
                        <li>Sunday: Closed</li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6 uppercase tracking-widest text-xs"><?php echo __('contact_us'); ?></h4>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        <i class="fas fa-map-marker-alt text-admin-secondary mr-2"></i> Jimma City, Oromia, Ethiopia<br>
                        <i class="fas fa-phone text-admin-secondary mr-2 text-xs"></i> +251 911 223 344<br>
                        <i class="fas fa-envelope text-admin-secondary mr-2 text-xs"></i> contact@ifabula.gov.et
                    </p>
                </div>
            </div>
            <div class="border-t border-white/5 pt-8 text-center text-slate-600 text-xs mt-8">
                &copy; <?php echo date('Y'); ?> Jimma Zone ICT Department | Empowering Jimma Digitally.
            </div>
        </div>
    </footer>

    <!-- AOS Script -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
    </script>
</body>
</html>
