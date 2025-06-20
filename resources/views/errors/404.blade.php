<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interactive 404 Page | Elites College</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        
        .float { animation: float 6s ease-in-out infinite; }
        .float-1 { animation-delay: 0s; }
        .float-2 { animation-delay: 1s; }
        .float-3 { animation-delay: 2s; }
        .float-4 { animation-delay: 3s; }
        
        .chaser {
            position: fixed;
            z-index: 10;
            pointer-events: none;
            transition: transform 0.1s linear;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .dark .glass-effect {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(100, 116, 139, 0.2);
        }
        
        .glow {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.5), 0 0 40px rgba(124, 58, 237, 0.3);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 50%, #dbeafe 100%);
        }
        
        .dark .gradient-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
        }
        
        .trail {
            position: absolute;
            border-radius: 50%;
            background: rgba(59, 130, 246, 0.3);
            pointer-events: none;
            z-index: 5;
        }
        
        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }
        
        .dark .theme-toggle {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(100, 116, 139, 0.3);
        }
        
        .theme-toggle:hover {
            transform: rotate(15deg) scale(1.1);
            background: rgba(255, 255, 255, 0.3);
        }
        
        .dark .theme-toggle:hover {
            background: rgba(15, 23, 42, 0.7);
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen overflow-hidden relative font-sans dark:bg-slate-900 transition-colors duration-500">
    <!-- Theme toggle -->
    <div class="theme-toggle" id="themeToggle">
        <i class="fas fa-moon text-yellow-400 text-xl dark:hidden"></i>
        <i class="fas fa-sun text-white text-xl hidden dark:block"></i>
    </div>
    
    <!-- Floating background elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute w-96 h-96 bg-blue-200 rounded-full top-1/4 left-1/4 blur-3xl opacity-30 animate-pulse dark:bg-blue-900/20"></div>
        <div class="absolute w-80 h-80 bg-purple-200 rounded-full top-1/3 right-1/4 blur-3xl opacity-30 animate-pulse dark:bg-purple-900/20"></div>
        <div class="absolute w-64 h-64 bg-cyan-200 rounded-full bottom-1/4 left-1/3 blur-3xl opacity-30 animate-pulse dark:bg-cyan-900/20"></div>
    </div>
    
    <!-- Chaser elements container -->
    <div id="chaserContainer"></div>
    
    <!-- Main content -->
    <div class="relative z-20 max-w-4xl mx-auto px-4 py-16 min-h-screen flex flex-col justify-center items-center">
        <div class="glass-effect rounded-3xl p-8 md:p-12 shadow-2xl w-full max-w-3xl text-center animate__animated animate__fadeInUp pulse">
            <div class="text-9xl font-bold mb-4 text-blue-700 dark:text-blue-400">
                <span class="text-blue-600 dark:text-blue-400">4</span>
                <span class="text-purple-600 dark:text-purple-400">0</span>
                <span class="text-cyan-600 dark:text-cyan-400">4</span>
            </div>
            
            <h1 class="text-4xl md:text-5xl font-bold text-slate-800 dark:text-white mb-6">
                Page Not Found
            </h1>
            
            <p class="text-xl text-slate-600 dark:text-gray-300 mb-10 max-w-2xl mx-auto">
                The page you're looking for might have been removed or doesn't exist. But don't worry, you can explore our amazing school instead!
            </p>
            
            <div class="flex flex-wrap justify-center gap-4">
                <a href="dashboard" class="px-8 py-4 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-bold rounded-full shadow-lg transform transition-all duration-300 hover:scale-105 hover:shadow-xl">
                    <i class="fas fa-home mr-2"></i>Go to Dashboard
                </a>
                <a href="/" class="px-8 py-4 bg-white/20 text-slate-800 dark:text-white font-bold rounded-full shadow-lg border border-white/30 transform transition-all duration-300 hover:scale-105 hover:bg-white/30 dark:bg-slate-800/50 dark:hover:bg-slate-800/70">
                    <i class="fas fa-arrow-left mr-2"></i>Go Homepage
                </a>
            </div>
        </div>
        
        <div class="mt-16 grid grid-cols-2 sm:grid-cols-4 gap-6 max-w-3xl w-full">
            <div class="glass-effect p-6 rounded-2xl text-center group transform transition-all duration-300 hover:scale-105">
                <div class="w-16 h-16 bg-blue-200/50 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-300/50 dark:group-hover:bg-blue-900/50">
                    <i class="fas fa-book text-3xl text-blue-600 dark:text-blue-400"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">Academics</h3>
            </div>
            <div class="glass-effect p-6 rounded-2xl text-center group transform transition-all duration-300 hover:scale-105">
                <div class="w-16 h-16 bg-purple-200/50 dark:bg-purple-900/30 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-purple-300/50 dark:group-hover:bg-purple-900/50">
                    <i class="fas fa-calendar-alt text-3xl text-purple-600 dark:text-purple-400"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">Events</h3>
            </div>
            <div class="glass-effect p-6 rounded-2xl text-center group transform transition-all duration-300 hover:scale-105">
                <div class="w-16 h-16 bg-cyan-200/50 dark:bg-cyan-900/30 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-cyan-300/50 dark:group-hover:bg-cyan-900/50">
                    <i class="fas fa-graduation-cap text-3xl text-cyan-600 dark:text-cyan-400"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">Admissions</h3>
            </div>
            <div class="glass-effect p-6 rounded-2xl text-center group transform transition-all duration-300 hover:scale-105">
                <div class="w-16 h-16 bg-blue-200/50 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-300/50 dark:group-hover:bg-blue-900/50">
                    <i class="fas fa-users text-3xl text-blue-600 dark:text-blue-400"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">Community</h3>
            </div>
        </div>
        
        <div class="mt-16 text-center text-slate-600 dark:text-gray-400 text-sm">
            <p>Elites International College • Excellence in Education Since 1998</p>
            <p class="mt-2 animate-pulse">Move your mouse around to see the magic!</p>
        </div>
    </div>
    
    <!-- Floating decorative elements -->
    <div class="absolute top-10 left-10 text-blue-500 float float-1">
        <i class="fas fa-atom text-5xl opacity-30"></i>
    </div>
    <div class="absolute top-20 right-20 text-purple-500 float float-2">
        <i class="fas fa-globe-americas text-5xl opacity-30"></i>
    </div>
    <div class="absolute bottom-20 left-1/4 text-cyan-500 float float-3">
        <i class="fas fa-flask text-5xl opacity-30"></i>
    </div>
    <div class="absolute bottom-1/3 right-1/3 text-blue-500 float float-4">
        <i class="fas fa-palette text-5xl opacity-30"></i>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Theme toggle
            const themeToggle = document.getElementById('themeToggle');
            themeToggle.addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');
                
                // Save theme preference
                const isDark = document.documentElement.classList.contains('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            });
            
            // Check for saved theme preference
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark');
            } else if (localStorage.getItem('theme') === 'light') {
                document.documentElement.classList.remove('dark');
            }
            
            // Create chaser elements
            const container = document.getElementById('chaserContainer');
            const chaserCount = 20;
            const icons = [
                'fa-book', 'fa-pencil-alt', 'fa-calculator', 'fa-flask', 
                'fa-globe', 'fa-music', 'fa-paint-brush', 'fa-basketball-ball',
                'fa-graduation-cap', 'fa-microscope', 'fa-atom', 'fa-laptop-code',
                'fa-robot', 'fa-dna', 'fa-chart-bar', 'fa-music', 'fa-palette',
                'fa-football-ball', 'fa-chess', 'fa-history'
            ];
            const colors = ['#3b82f6', '#8b5cf6', '#0ea5e9', '#06b6d4', '#14b8a6', '#10b981'];
            
            // Create chaser elements
            const chasers = [];
            
            for (let i = 0; i < chaserCount; i++) {
                const chaser = document.createElement('div');
                chaser.className = 'chaser';
                
                // Random icon
                const icon = icons[Math.floor(Math.random() * icons.length)];
                const color = colors[Math.floor(Math.random() * colors.length)];
                const size = 24 + Math.floor(Math.random() * 24); // 24px to 48px
                
                chaser.innerHTML = `<i class="${icon}" style="color: ${color}; font-size: ${size}px"></i>`;
                
                // Random starting position
                const startX = Math.random() * window.innerWidth;
                const startY = Math.random() * window.innerHeight;
                chaser.style.left = `${startX}px`;
                chaser.style.top = `${startY}px`;
                
                // Store position data
                chaser.x = startX;
                chaser.y = startY;
                chaser.vx = 0;
                chaser.vy = 0;
                chaser.speed = 0.1 + Math.random() * 0.1;
                chaser.size = size;
                
                container.appendChild(chaser);
                chasers.push(chaser);
            }
            
            // Mouse position
            let mouseX = window.innerWidth / 2;
            let mouseY = window.innerHeight / 2;
            
            document.addEventListener('mousemove', (e) => {
                mouseX = e.clientX;
                mouseY = e.clientY;
                
                // Create trail effect
                createTrail(e.clientX, e.clientY);
            });
            
            // Create trail effect
            function createTrail(x, y) {
                const trail = document.createElement('div');
                trail.className = 'trail';
                trail.style.left = `${x}px`;
                trail.style.top = `${y}px`;
                
                // Random size
                const size = Math.random() * 20 + 5;
                trail.style.width = `${size}px`;
                trail.style.height = `${size}px`;
                
                // Random color
                const colorIndex = Math.floor(Math.random() * colors.length);
                trail.style.backgroundColor = `${colors[colorIndex]}30`;
                
                document.body.appendChild(trail);
                
                // Fade out and remove
                setTimeout(() => {
                    trail.style.opacity = '0';
                    trail.style.transform = 'scale(2)';
                    setTimeout(() => {
                        document.body.removeChild(trail);
                    }, 500);
                }, 100);
            }
            
            // Animation loop
            function animate() {
                chasers.forEach(chaser => {
                    // Calculate direction to mouse
                    const dx = mouseX - chaser.x;
                    const dy = mouseY - chaser.y;
                    
                    // Calculate distance
                    const distance = Math.sqrt(dx * dx + dy * dy);
                    
                    // Normalize direction
                    if (distance > 0) {
                        const ax = dx / distance * chaser.speed;
                        const ay = dy / distance * chaser.speed;
                        
                        // Apply acceleration
                        chaser.vx += ax;
                        chaser.vy += ay;
                        
                        // Apply friction
                        chaser.vx *= 0.95;
                        chaser.vy *= 0.95;
                        
                        // Update position
                        chaser.x += chaser.vx;
                        chaser.y += chaser.vy;
                        
                        // Apply position
                        chaser.style.transform = `translate(${chaser.x - chaser.size/2}px, ${chaser.y - chaser.size/2}px)`;
                        
                        // Rotate towards movement direction
                        const rotation = Math.atan2(chaser.vy, chaser.vx) * 180 / Math.PI;
                        chaser.style.transform += ` rotate(${rotation + 90}deg)`;
                    }
                });
                
                requestAnimationFrame(animate);
            }
            
            animate();
            
            // Add some random movement when mouse isn't moving
            setInterval(() => {
                if (Math.abs(mouseX - chasers[0].x) < 50 && Math.abs(mouseY - chasers[0].y) < 50) {
                    mouseX = Math.random() * window.innerWidth;
                    mouseY = Math.random() * window.innerHeight;
                }
            }, 3000);
        });
    </script>
</body>
</html>