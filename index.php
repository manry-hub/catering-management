<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kantin Gorjes | Luxury Dining Experience</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        luxury: {
                            bg: '#F9F8F6',
                            fg: '#1A1A1A',
                            accent: '#D4AF37',
                            mutedFg: '#666666',
                            white: '#FFFFFF'
                        }
                    },
                    fontFamily: {
                        serif: ['Playfair Display', 'serif'],
                        sans: ['Inter', 'sans-serif'],
                    },
                    boxShadow: {
                        'btn': '0 4px 14px 0 rgba(0, 0, 0, 0.1)',
                        'btn-hover': '0 6px 20px rgba(0, 0, 0, 0.15)',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #F9F8F6;
            color: #1A1A1A;
            font-family: 'Inter', sans-serif;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.05'/%3E%3C/svg%3E");
        }
        
        /* Grid Lines for architectural look */
        .bg-grid {
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(26, 26, 26, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(26, 26, 26, 0.05) 1px, transparent 1px);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-luxury-accent selection:text-white">

    <!-- Navigation -->
    <nav class="w-full border-b border-luxury-fg/10 py-6 px-8 relative z-10 flex justify-between items-center bg-luxury-bg/80 backdrop-blur-md fixed top-0">
        <div class="font-serif text-2xl font-semibold tracking-tight">Kantin <span class="italic text-luxury-accent">Gorjes</span></div>
        <div class="flex items-center space-x-6">
            <a href="login.php" class="text-xs tracking-[0.2em] uppercase font-medium text-luxury-mutedFg hover:text-luxury-fg transition-colors">Login</a>
            <a href="login.php" class="h-10 px-6 bg-luxury-fg text-luxury-white text-xs tracking-[0.2em] uppercase font-medium flex items-center justify-center relative overflow-hidden group shadow-btn hover:shadow-btn-hover transition-all duration-500">
                <span class="absolute inset-0 bg-luxury-accent -translate-x-full group-hover:translate-x-0 transition-transform duration-500" style="transition-timing-function: cubic-bezier(0.25, 0.46, 0.45, 0.94);"></span>
                <span class="relative z-10 text-white">Dashboard</span>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow bg-grid relative pt-24 flex items-center justify-center overflow-hidden">
        
        <!-- Decorative Elements -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] border border-luxury-fg/5 rounded-full pointer-events-none"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] border border-luxury-fg/5 rounded-full pointer-events-none"></div>
        
        <div class="container mx-auto px-6 py-20 relative z-10 flex flex-col items-center text-center">
            
            <p class="text-luxury-accent text-xs tracking-[0.3em] uppercase mb-6 font-medium">Est. 2026</p>
            
            <h1 class="font-serif text-6xl md:text-8xl tracking-tighter mb-8 max-w-4xl text-luxury-fg leading-[1.1]">
                A Symphony of <br>
                <span class="italic font-light text-luxury-mutedFg">Taste & Elegance</span>
            </h1>
            
            <p class="text-luxury-mutedFg max-w-xl text-lg font-light leading-relaxed mb-12">
                Experience the pinnacle of culinary excellence. Kantin Gorjes brings you a meticulously curated menu, blending traditional flavors with modern sophistication.
            </p>
            
            <div class="flex items-center space-x-6">
                <a href="#explore" class="h-14 px-8 bg-transparent border border-luxury-fg text-luxury-fg text-xs tracking-[0.2em] uppercase font-medium flex items-center justify-center hover:bg-luxury-fg hover:text-luxury-white transition-colors duration-500">
                    Explore Menu
                </a>
                <a href="login.php" class="h-14 px-8 bg-luxury-fg text-luxury-white text-xs tracking-[0.2em] uppercase font-medium flex items-center justify-center relative overflow-hidden group shadow-btn hover:shadow-btn-hover transition-all duration-500">
                    <span class="absolute inset-0 bg-luxury-accent -translate-x-full group-hover:translate-x-0 transition-transform duration-500" style="transition-timing-function: cubic-bezier(0.25, 0.46, 0.45, 0.94);"></span>
                    <span class="relative z-10 text-white">Admin Access</span>
                </a>
            </div>
            
        </div>
        
        <!-- Bottom Left / Right Accents -->
        <div class="absolute bottom-12 left-12 hidden md:block">
            <p class="text-[10px] tracking-widest uppercase text-luxury-mutedFg transform -rotate-90 origin-bottom-left">Exclusive</p>
        </div>
        <div class="absolute bottom-12 right-12 hidden md:block text-right">
            <p class="text-[10px] tracking-widest uppercase text-luxury-mutedFg">Jakarta, ID</p>
        </div>
        
    </main>

    <!-- Simple Footer -->
    <footer class="w-full border-t border-luxury-fg/10 py-8 px-12 flex flex-col md:flex-row justify-between items-center text-xs text-luxury-mutedFg tracking-widest uppercase relative z-10 bg-luxury-bg">
        <p>&copy; <?= date('Y') ?> Kantin Gorjes. All Rights Reserved.</p>
        <div class="flex space-x-6 mt-4 md:mt-0">
            <a href="#" class="hover:text-luxury-accent transition-colors">Privacy Policy</a>
            <a href="#" class="hover:text-luxury-accent transition-colors">Terms of Service</a>
        </div>
    </footer>

</body>
</html>
