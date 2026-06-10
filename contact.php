<?php
// contact.php - Public Contact Page
require_once __DIR__ . '/includes/lang.php';

$success_msg = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Just a stub for visual success message
    $success_msg = __("contact_success");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('nav_contact'); ?> | BOSA ADDIS KEBELE MANAGEMENT SYSTEM</title>
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
        .hero-gradient { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); }
    </style>
</head>
<body class="bg-admin-dark text-slate-200 font-sans selection:bg-admin-secondary selection:text-white">

    <!-- Unified Navbar -->
    <?php include __DIR__ . '/includes/public_navbar.php'; ?>

    <main class="pt-40 pb-24">
        <div class="container mx-auto px-6 max-w-6xl">
            <!-- Header -->
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-admin-secondary font-bold uppercase tracking-[0.3em] text-sm mb-4"><?php echo __('get_in_touch'); ?></h2>
                <h1 class="text-5xl md:text-6xl font-display font-extrabold text-white mb-6"><?php echo __('nav_contact'); ?></h1>
                <p class="text-xl text-slate-400 max-w-2xl mx-auto leading-relaxed">
                    <?php echo __('contact_desc'); ?>
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-12">
                <!-- Contact Info -->
                <div class="space-y-8" data-aos="fade-right">
                    <div class="glass p-8 rounded-3xl border border-white/5 hover:border-admin-secondary/30 transition-all">
                        <div class="w-14 h-14 rounded-2xl bg-admin-secondary/10 flex items-center justify-center text-admin-secondary text-2xl mb-6">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2"><?php echo __('visit_us'); ?></h3>
                        <p class="text-slate-400 leading-relaxed"><?php echo __('jimma_city'); ?><br><?php echo __('zone'); ?> Jimma, Oromia</p>
                    </div>

                    <div class="glass p-8 rounded-3xl border border-white/5 hover:border-admin-secondary/30 transition-all">
                        <div class="w-14 h-14 rounded-2xl bg-admin-secondary/10 flex items-center justify-center text-emerald-500 text-2xl mb-6">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2"><?php echo __('call_us'); ?></h3>
                        <p class="text-slate-400 leading-relaxed">+251 934 953 593<br><?php echo __('official_support'); ?>: +251 911 000 111</p>
                    </div>

                    <div class="glass p-8 rounded-3xl border border-white/5 hover:border-admin-secondary/30 transition-all">
                        <div class="w-14 h-14 rounded-2xl bg-admin-secondary/10 flex items-center justify-center text-rose-500 text-2xl mb-6">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2"><?php echo __('email_us'); ?></h3>
                        <p class="text-slate-400 leading-relaxed text-sm">support@kebele.gov.et<br>info@kebele.gov.et</p>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="md:col-span-2 glass p-10 rounded-3xl border border-white/5" data-aos="fade-left">
                    <h3 class="text-3xl font-display font-bold text-white mb-8"><?php echo __('send_message'); ?></h3>
                    
                    <?php if($success_msg): ?>
                        <div class="mb-8 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl flex items-center gap-3">
                            <i class="fas fa-check-circle"></i>
                            <p><?php echo htmlspecialchars($success_msg); ?></p>
                        </div>
                    <?php endif; ?>

                    <form action="contact.php" method="POST" class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-slate-400 text-sm font-bold uppercase tracking-wider"><?php echo __('full_name'); ?></label>
                                <input type="text" name="name" required placeholder="..." class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-secondary transition-colors">
                            </div>
                            <div class="space-y-2">
                                <label class="text-slate-400 text-sm font-bold uppercase tracking-wider"><?php echo __('email_addr'); ?></label>
                                <input type="email" name="email" required placeholder="..." class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-secondary transition-colors">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-slate-400 text-sm font-bold uppercase tracking-wider"><?php echo __('subject'); ?></label>
                            <input type="text" name="subject" required placeholder="..." class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-secondary transition-colors">
                        </div>

                        <div class="space-y-2">
                            <label class="text-slate-400 text-sm font-bold uppercase tracking-wider"><?php echo __('message'); ?></label>
                            <textarea name="message" required rows="6" placeholder="<?php echo __('how_assist'); ?>" class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-secondary transition-colors"></textarea>
                        </div>

                        <button type="submit" class="w-full bg-admin-primary hover:bg-indigo-500 text-white px-8 py-4 rounded-xl font-bold transition-all shadow-[0_0_20px_rgba(79,70,229,0.4)] hover:shadow-[0_0_30px_rgba(79,70,229,0.6)] text-lg">
                            <?php echo __('send_btn'); ?> <i class="fas fa-paper-plane ml-2"></i>
                        </button>
                    </form>
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
        AOS.init({ duration: 1000, once: true, offset: 100 });
    </script>
</body>
</html>
