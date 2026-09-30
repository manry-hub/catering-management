<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kantin Gorjes</title>
    <!-- Google Fonts: Playfair Display & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,300;0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS (CDN for Luxury UI) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        luxury: {
                            bg: '#F9F8F6',
                            fg: '#1A1A1A',
                            mutedBg: '#EBE5DE',
                            mutedFg: '#6C6863',
                            accent: '#D4AF37',
                            white: '#FFFFFF'
                        }
                    },
                    fontFamily: {
                        serif: ['"Playfair Display"', 'serif'],
                        sans: ['Inter', 'sans-serif'],
                    },
                    boxShadow: {
                        'hero': '0 8px 32px rgba(0,0,0,0.12)',
                        'card': '0 2px 8px rgba(0,0,0,0.02)',
                        'card-hover': '0 8px 24px rgba(0,0,0,0.06)',
                        'btn': '0 4px 16px rgba(0,0,0,0.15)',
                        'btn-hover': '0 8px 24px rgba(0,0,0,0.25)',
                        'inner': 'inset 0 0 0 1px rgba(0,0,0,0.04)',
                    },
                    transitionTimingFunction: {
                        'luxury': 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #F9F8F6;
            color: #1A1A1A;
            border-radius: 0px !important;
        }

        /* Enforce global 0px border radius */
        * {
            border-radius: 0px !important;
        }

        /* SVG Noise Texture Overlay */
        .noise-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 50;
            opacity: 0.02;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
        }

        /* 4 Vertical Gridlines */
        .gridline-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            max-width: 1600px;
            margin: 0 auto;
            padding: 0 4rem; /* px-16 desktop equivalent */
            left: 50%;
            transform: translateX(-50%);
        }
        @media (max-width: 768px) {
            .gridline-container {
                padding: 0 2rem; /* px-8 mobile equivalent */
            }
        }
        .gridline-col {
            border-left: 1px solid rgba(26, 26, 26, 0.08);
            height: 100%;
        }
        .gridline-col:last-child {
            border-right: 1px solid rgba(26, 26, 26, 0.08);
        }

        /* Underline Inputs */
        .glass-input {
            background: transparent !important;
            border: none !important;
            border-bottom: 1px solid rgba(26, 26, 26, 0.2) !important;
            border-radius: 0 !important;
            padding: 0.5rem 0 !important;
            height: 3rem !important; /* h-12 */
            transition: all 0.3s ease;
        }
        .glass-input:focus {
            border-color: #D4AF37 !important;
            box-shadow: none !important;
        }

        /* Cards / Panels */
        .glass-panel {
            background: transparent;
            border: none;
            border-top: 1px solid rgba(26, 26, 26, 0.2);
            box-shadow: none;
            transition: background-color 0.5s ease;
        }
        .glass-panel:hover {
            background-color: rgba(249, 248, 246, 0.5); /* subtle shift */
        }
        
        /* Tables */
        table {
            border-radius: 0;
            border-collapse: collapse;
            background: transparent;
            box-shadow: none;
            border: none;
        }
        th, td {
            border-bottom: 1px solid rgba(26,26,26,0.1) !important;
        }
        th {
            background: transparent;
        }
    </style>
</head>
<body class="font-sans antialiased text-luxury-fg min-h-screen flex flex-col">
    <!-- Luxury Noise Texture -->
    <div class="noise-overlay"></div>

    <!-- Editorial Vertical Gridlines -->
    <div class="gridline-container">
        <div class="gridline-col col-start-1"></div>
        <div class="gridline-col col-start-4"></div>
        <div class="gridline-col col-start-7"></div>
        <div class="gridline-col col-start-10"></div>
    </div>
