<?php
// services.php - Enhanced Services Catalog
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/lang.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('title_services'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
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
        .card-hover:hover { transform: translateY(-12px); border-color: rgba(14, 165, 233, 0.4); box-shadow: 0 20px 40px -15px rgba(0,0,0,0.5); }
        .service-img { height: 240px; object-fit: cover; width: 100%; transition: transform 0.5s ease; }
        .card-hover:hover .service-img { transform: scale(1.1); }
    </style>
</head>
<body class="bg-admin-dark text-slate-200 font-sans selection:bg-admin-secondary selection:text-white">

    <!-- Unified Navbar -->
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
            <a href="services.php" class="text-admin-secondary font-bold"><?php echo __('services'); ?></a>
            <a href="stats.php" class="hover:text-admin-secondary transition-colors"><?php echo __('stats'); ?></a>
            <a href="about.php" class="hover:text-admin-secondary transition-colors"><?php echo __('about'); ?></a>
        </div>
        
        <div class="flex items-center gap-6">
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

    <main class="pt-40 pb-24">
        <div class="container mx-auto px-6">
            <!-- Section Header -->
            <div class="max-w-4xl mx-auto text-center mb-24" data-aos="fade-up">
                <h2 class="text-admin-secondary font-bold uppercase tracking-[0.3em] text-sm mb-4"><?php echo __('service_catalog_title'); ?></h2>
                <h1 class="text-5xl md:text-7xl font-display font-extrabold text-white mb-8 leading-tight"><?php echo __('service_catalog_subtitle'); ?></h1>
                <p class="text-xl text-slate-400 leading-relaxed max-w-3xl mx-auto">
                    <?php echo __('hero_desc'); ?>
                </p>
            </div>

            <!-- Service Catalog Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-10">
                
                <!-- 1. Resident Identification -->
                <div class="glass group overflow-hidden rounded-3xl border border-white/5 card-hover transition-all duration-500" data-aos="fade-up" data-aos-delay="100">
                    <div class="relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1613243555988-441166d4d6fd?q=80&w=800&auto=format&fit=crop" alt="ID Card" class="service-img">
                        <div class="absolute inset-0 bg-gradient-to-t from-admin-dark to-transparent opacity-60"></div>
                        <div class="absolute bottom-6 left-6">
                            <span class="bg-admin-secondary/20 text-admin-secondary text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full border border-admin-secondary/30 backdrop-blur-md"><?php echo __('service_primary_credential'); ?></span>
                        </div>
                    </div>
                    <div class="p-8">
                        <h3 class="text-2xl font-display font-bold text-white mb-4"><?php echo __('service_id_title'); ?></h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6">
                            <?php echo __('service_id_desc'); ?>
                        </p>
                        <div class="flex items-center justify-between text-xs font-bold uppercase tracking-widest text-slate-500 pt-4 border-t border-white/5">
                            <span><?php echo __('service_processing_time'); ?>: <?php echo __('service_processing_time_val'); ?></span>
                            <span class="text-admin-secondary"><?php echo __('learn_more'); ?> <i class="fas fa-chevron-right ml-1"></i></span>
                        </div>
                    </div>
                </div>

                <!-- 2. Birth Certification -->
                <div class="glass group overflow-hidden rounded-3xl border border-white/5 card-hover transition-all duration-500" data-aos="fade-up" data-aos-delay="200">
                    <div class="relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1544126592-807daa2b56fd?q=80&w=800&auto=format&fit=crop" alt="Birth Certificate" class="service-img">
                        <div class="absolute inset-0 bg-gradient-to-t from-admin-dark to-transparent opacity-60"></div>
                        <div class="absolute bottom-6 left-6">
                            <span class="bg-emerald-500/20 text-emerald-400 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full border border-emerald-500/30 backdrop-blur-md"><?php echo __('service_vital_record'); ?></span>
                        </div>
                    </div>
                    <div class="p-8">
                        <h3 class="text-2xl font-display font-bold text-white mb-4"><?php echo __('service_civil_title'); ?></h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6">
                            <?php echo __('service_civil_desc'); ?>
                        </p>
                        <div class="flex items-center justify-between text-xs font-bold uppercase tracking-widest text-slate-500 pt-4 border-t border-white/5">
                            <span><?php echo __('legacy_support'); ?></span>
                            <span class="text-admin-secondary"><?php echo __('learn_more'); ?> <i class="fas fa-chevron-right ml-1"></i></span>
                        </div>
                    </div>
                </div>

                <!-- 3. Marriage Registration -->
                <div class="glass group overflow-hidden rounded-3xl border border-white/5 card-hover transition-all duration-500" data-aos="fade-up" data-aos-delay="300">
                    <div class="relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1515934751635-c81c6bc9a2d8?q=80&w=800&auto=format&fit=crop" alt="Marriage" class="service-img">
                        <div class="absolute inset-0 bg-gradient-to-t from-admin-dark to-transparent opacity-60"></div>
                        <div class="absolute bottom-6 left-6">
                            <span class="bg-rose-500/20 text-rose-400 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full border border-rose-500/30 backdrop-blur-md"><?php echo __('service_civil_status'); ?></span>
                        </div>
                    </div>
                    <div class="p-8">
                        <h3 class="text-2xl font-display font-bold text-white mb-4"><?php echo __('service_marriage_title'); ?></h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6">
                            <?php echo __('service_marriage_desc'); ?>
                        </p>
                        <div class="flex items-center justify-between text-xs font-bold uppercase tracking-widest text-slate-500 pt-4 border-t border-white/5">
                            <span><?php echo __('multi_lang_support'); ?></span>
                            <span class="text-admin-secondary"><?php echo __('learn_more'); ?> <i class="fas fa-chevron-right ml-1"></i></span>
                        </div>
                    </div>
                </div>

                <!-- 4. Clearance Services -->
                <div class="glass group overflow-hidden rounded-3xl border border-white/5 card-hover transition-all duration-500" data-aos="fade-up" data-aos-delay="400">
                    <div class="relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1450101499163-c8848c66ca85?q=80&w=800&auto=format&fit=crop" alt="Clearance" class="service-img">
                        <div class="absolute inset-0 bg-gradient-to-t from-admin-dark to-transparent opacity-60"></div>
                        <div class="absolute bottom-6 left-6">
                            <span class="bg-purple-500/20 text-purple-400 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full border border-purple-500/30 backdrop-blur-md"><?php echo __('service_verification'); ?></span>
                        </div>
                    </div>
                    <div class="p-8">
                        <h3 class="text-2xl font-display font-bold text-white mb-4"><?php echo __('service_clearance_title'); ?></h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6">
                            <?php echo __('service_clearance_desc'); ?>
                        </p>
                        <div class="flex items-center justify-between text-xs font-bold uppercase tracking-widest text-slate-500 pt-4 border-t border-white/5">
                            <span><?php echo __('service_instant_qr'); ?></span>
                            <span class="text-admin-secondary"><?php echo __('learn_more'); ?> <i class="fas fa-chevron-right ml-1"></i></span>
                        </div>
                    </div>
                </div>

                <!-- 5. Housing & Property -->
                <div class="glass group overflow-hidden rounded-3xl border border-white/5 card-hover transition-all duration-500" data-aos="fade-up" data-aos-delay="500">
                    <div class="relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?q=80&w=800&auto=format&fit=crop" alt="Housing" class="service-img">
                        <div class="absolute inset-0 bg-gradient-to-t from-admin-dark to-transparent opacity-60"></div>
                        <div class="absolute bottom-6 left-6">
                            <span class="bg-amber-500/20 text-amber-400 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full border border-amber-500/30 backdrop-blur-md"><?php echo __('service_property_mgmt'); ?></span>
                        </div>
                    </div>
                    <div class="p-8">
                        <h3 class="text-2xl font-display font-bold text-white mb-4"><?php echo __('service_housing_title'); ?></h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6">
                            <?php echo __('service_housing_desc'); ?>
                        </p>
                        <div class="flex items-center justify-between text-xs font-bold uppercase tracking-widest text-slate-500 pt-4 border-t border-white/5">
                            <span><?php echo __('zone_mapping'); ?></span>
                            <span class="text-admin-secondary"><?php echo __('learn_more'); ?> <i class="fas fa-chevron-right ml-1"></i></span>
                        </div>
                    </div>
                </div>

                <!-- 6. Death Registration -->
                <div class="glass group overflow-hidden rounded-3xl border border-white/5 card-hover transition-all duration-500" data-aos="fade-up" data-aos-delay="600">
                    <div class="relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1586282391129-59a998fd034c?q=80&w=800&auto=format&fit=crop" alt="Death Certificate" class="service-img">
                        <div class="absolute inset-0 bg-gradient-to-t from-admin-dark to-transparent opacity-60"></div>
                        <div class="absolute bottom-6 left-6">
                            <span class="bg-slate-500/20 text-slate-400 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full border border-slate-500/30 backdrop-blur-md"><?php echo __('service_vital_record'); ?></span>
                        </div>
                    </div>
                    <div class="p-8">
                        <h3 class="text-2xl font-display font-bold text-white mb-4"><?php echo __('death_cert'); ?></h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6">
                            <?php echo __('death_impact_desc'); ?>
                        </p>
                        <div class="flex items-center justify-between text-xs font-bold uppercase tracking-widest text-slate-500 pt-4 border-t border-white/5">
                            <span><?php echo __('service_sla_time'); ?></span>
                            <span class="text-admin-secondary"><?php echo __('learn_more'); ?> <i class="fas fa-chevron-right ml-1"></i></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <footer class="bg-admin-dark border-t border-white/5 pt-16 pb-8">
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
