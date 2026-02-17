<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CBT System') }} - Exam Mode</title>

    <!-- Meta tags -->
    <meta name="google-adsense-account" content="ca-pub-3911204427206897">
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-10833921436"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'AW-10833921436');
    </script>

    <!-- Security headers -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <!-- Fonts and assets -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <!-- MathJax Configuration -->
    <script>
        window.MathJax = {
            tex: {
                inlineMath: [['$', '$'], ['\\(', '\\)']],
                displayMath: [['$$', '$$'], ['\\[', '\\]']]
            },
            svg: {
                fontCache: 'global'
            },
            startup: {
                pageReady: function () {
                    return MathJax.startup.defaultPageReady().then(function () {
                        document.dispatchEvent(new Event('mathjax-loaded'));
                    });
                }
            },
            options: {
                skipHtmlTags: ['script', 'noscript', 'style', 'textarea', 'pre'],
                renderActions: {
                    addMenu: []
                }
            }
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js" async></script>

    <style>
        /* ================================================
           MATHJAX STYLES - NON-INTERACTIVE
           ================================================ */
        mjx-container {
            all: revert !important;
            pointer-events: none !important;
        }
        
        mjx-container[jax="CHTML"] {
            cursor: default !important;
            pointer-events: none !important;
            user-select: none !important;
            -webkit-user-select: none !important;
            background: transparent !important;
            outline: none !important;
            border: none !important;
            box-shadow: none !important;
            text-decoration: none !important;
        }
        
        mjx-container[jax="CHTML"]:hover,
        mjx-container[jax="CHTML"]:focus {
            background-color: transparent !important;
            outline: none !important;
            text-decoration: none !important;
        }
        
        mjx-container mjx-math {
            pointer-events: none !important;
            text-decoration: none !important;
        }
        
        mjx-container {
            -webkit-touch-callout: none !important;
        }

        /* ================================================
           EXAM SECURITY STYLES
           ================================================ */
        
        /* Disable text selection in exam mode */
        body.exam-mode {
            user-select: none !important;
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            -webkit-touch-callout: none !important;
            touch-action: manipulation !important;
        }

        /* Allow selection only in input fields */
        body.exam-mode input,
        body.exam-mode textarea {
            user-select: text !important;
            -webkit-user-select: text !important;
        }

        /* Disable right-click context menu - Enhanced for mobile */
        body.exam-mode * {
            -webkit-touch-callout: none !important;
            -webkit-user-select: none !important;
            user-select: none !important;
        }

        /* Watermark overlay */
        body.exam-mode::before {
            content: attr(data-exam-info);
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 4rem;
            color: rgba(0, 0, 0, 0.03);
            pointer-events: none;
            z-index: 1;
            white-space: nowrap;
            font-weight: bold;
        }

        /* Fullscreen styles */
        body.exam-mode.fullscreen-forced {
            width: 100vw;
            height: 100vh;
            overflow: hidden;
        }

        /* Tab switch warning overlay */
        .tab-switch-warning {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(220, 38, 38, 0.95);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            color: white;
            animation: warningPulse 1s infinite;
        }

        @keyframes warningPulse {
            0%, 100% { opacity: 0.95; }
            50% { opacity: 1; }
        }

        .tab-switch-warning h1 {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }

        .tab-switch-warning p {
            font-size: 1.5rem;
            margin-bottom: 2rem;
        }

        .tab-switch-warning .violation-count {
            font-size: 5rem;
            font-weight: bold;
            margin: 1rem 0;
        }

        /* Security indicator positioning */
        .security-indicator {
            position: fixed;
            bottom: 80px;
            right: 20px;
            background: rgba(16, 185, 129, 0.9);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            z-index: 50;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Mobile adjustment */
        @media (max-width: 640px) {
            .security-indicator {
                bottom: 100px;
                right: 10px;
                font-size: 0.75rem;
                padding: 0.375rem 0.75rem;
            }
        }

        .security-indicator.warning {
            background: rgba(234, 179, 8, 0.9);
        }

        .security-indicator.danger {
            background: rgba(239, 68, 68, 0.9);
        }

        .security-indicator .pulse-dot {
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }

        /* Copy detection overlay */
        .copy-detection-flash {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(239, 68, 68, 0.3);
            pointer-events: none;
            z-index: 9996;
            animation: flashRed 0.5s;
        }

        @keyframes flashRed {
            0%, 100% { opacity: 0; }
            50% { opacity: 1; }
        }
    </style>

    @stack('styles')
</head>

<body class="h-full bg-gray-50 dark:bg-gray-900 font-sans antialiased" 
      id="examBody"
      data-exam-info="{{ $title ?? 'CBT Exam' }} - {{ now()->format('Y-m-d') }}">
    
    {{-- Security Warning Overlay (Hidden by default, shown on tab switch) --}}
    <div id="tabSwitchWarning" class="tab-switch-warning" style="display: none;">
        <i class="fas fa-exclamation-triangle" style="font-size: 5rem; margin-bottom: 2rem;"></i>
        <h1>⚠️ WARNING ⚠️</h1>
        <p>Tab switching detected!</p>
        <div class="violation-count" id="violationCount">0</div>
        <p>Violations recorded. Your exam may be flagged.</p>
        <button onclick="hideTabWarning()" 
                style="margin-top: 2rem; padding: 1rem 3rem; background: white; color: #dc2626; 
                       border: none; border-radius: 0.5rem; font-size: 1.25rem; font-weight: bold; 
                       cursor: pointer;">
            Return to Exam
        </button>
    </div>

    {{-- Security Indicator --}}
    <div id="securityIndicator" class="security-indicator" style="display: none;">
        <div class="pulse-dot"></div>
        <span id="securityStatus">Secure Mode</span>
    </div>

    {{-- Main Content --}}
    <main class="h-full">
        {{ $slot }}
    </main>

    @livewireScripts
    
    {{-- Enhanced Security Script - PRODUCTION READY - FIXED VERSION --}}
    <script>
        // ================================================
        // EXAM SECURITY SYSTEM - FIXED VERSION
        // ================================================
        
        const ExamSecurity = {
            violations: 0,
            maxViolations: 5,
            isExamActive: false,
            tabSwitchCount: 0,
            copyAttempts: 0,
            rightClickAttempts: 0,
            autoSubmitPending: false,
            isFullscreenActive: false,
            
            init() {
                this.setupEventListeners();
                this.preventDevTools();
            },
            
            activate() {
                this.isExamActive = true;
                document.body.classList.add('exam-mode');
                this.showSecurityIndicator('Secure Mode', 'success');
                this.requestFullscreen();
            },
            
            deactivate() {
                this.isExamActive = false;
                document.body.classList.remove('exam-mode', 'fullscreen-forced');
                this.hideSecurityIndicator();
                this.exitFullscreen();
            },
            
            setupEventListeners() {
                // ENHANCED: Prevent right-click on ALL devices including mobile
                const preventContextMenu = (e) => {
                    if (this.isExamActive) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        this.rightClickAttempts++;
                        this.recordViolation('right_click');
                        this.showFlash();
                        return false;
                    }
                };

                // Desktop right-click
                document.addEventListener('contextmenu', preventContextMenu, { capture: true, passive: false });
                
                // Mobile long-press (triggers context menu)
                let longPressTimer;
                document.addEventListener('touchstart', (e) => {
                    if (!this.isExamActive) return;
                    
                    longPressTimer = setTimeout(() => {
                        e.preventDefault();
                        this.rightClickAttempts++;
                        this.recordViolation('long_press_attempt');
                        this.showFlash();
                    }, 500); // 500ms = long press
                }, { passive: false });
                
                document.addEventListener('touchend', () => {
                    clearTimeout(longPressTimer);
                }, { passive: false });
                
                document.addEventListener('touchmove', () => {
                    clearTimeout(longPressTimer);
                }, { passive: false });

                // Prevent iOS callout menu
                document.addEventListener('touchstart', (e) => {
                    if (this.isExamActive && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                        e.target.style.webkitTouchCallout = 'none';
                    }
                }, { passive: false });
                
                // Prevent copy
                document.addEventListener('copy', (e) => {
                    if (this.isExamActive) {
                        e.preventDefault();
                        this.copyAttempts++;
                        this.recordViolation('copy_attempt');
                        this.showFlash();
                        this.showSecurityIndicator('Copy Disabled', 'warning');
                        setTimeout(() => {
                            this.showSecurityIndicator('Secure Mode', 'success');
                        }, 2000);
                        return false;
                    }
                });
                
                // Prevent cut
                document.addEventListener('cut', (e) => {
                    if (this.isExamActive) {
                        e.preventDefault();
                        this.recordViolation('cut_attempt');
                        return false;
                    }
                });
                
                // Prevent paste (except in input fields)
                document.addEventListener('paste', (e) => {
                    if (this.isExamActive && !['INPUT', 'TEXTAREA'].includes(e.target.tagName)) {
                        e.preventDefault();
                        return false;
                    }
                });
                
                // Prevent keyboard shortcuts
                document.addEventListener('keydown', (e) => {
                    if (!this.isExamActive) return;
                    
                    if (e.key === 'F12' || e.keyCode === 123) {
                        e.preventDefault();
                        this.recordViolation('devtools_attempt');
                        return false;
                    }
                    
                    if (e.ctrlKey && e.shiftKey && ['I', 'J', 'C'].includes(e.key.toUpperCase())) {
                        e.preventDefault();
                        this.recordViolation('devtools_attempt');
                        return false;
                    }
                    
                    if (e.ctrlKey && e.key === 'u') {
                        e.preventDefault();
                        this.recordViolation('view_source_attempt');
                        return false;
                    }
                    
                    if (e.ctrlKey && e.key === 's') {
                        e.preventDefault();
                        return false;
                    }
                    
                    if (e.ctrlKey && e.key === 'p') {
                        e.preventDefault();
                        return false;
                    }
                });
                
                // Detect tab/window switching
                document.addEventListener('visibilitychange', () => {
                    if (this.isExamActive && document.hidden) {
                        this.tabSwitchCount++;
                        this.recordViolation('tab_switch');
                        this.showTabSwitchWarning();
                    }
                });
                
                // Detect window blur (lost focus)
                window.addEventListener('blur', () => {
                    if (this.isExamActive) {
                        this.recordViolation('window_blur');
                        this.showSecurityIndicator('Focus Lost', 'danger');
                    }
                });
                
                // Detect window focus return
                window.addEventListener('focus', () => {
                    if (this.isExamActive) {
                        this.showSecurityIndicator('Secure Mode', 'success');
                    }
                });
                
                // FIXED: Detect fullscreen exit with proper error handling
                document.addEventListener('fullscreenchange', () => {
                    if (this.isExamActive && !document.fullscreenElement) {
                        this.isFullscreenActive = false;
                        this.recordViolation('fullscreen_exit');
                        this.showSecurityIndicator('Fullscreen Required', 'danger');
                        setTimeout(() => {
                            if (this.isExamActive) {
                                this.requestFullscreen();
                            }
                        }, 2000);
                    } else if (document.fullscreenElement) {
                        this.isFullscreenActive = true;
                    }
                });
                
                // Prevent text selection
                document.addEventListener('selectstart', (e) => {
                    if (this.isExamActive && !['INPUT', 'TEXTAREA'].includes(e.target.tagName)) {
                        e.preventDefault();
                        return false;
                    }
                });
                
                // Detect screenshot attempts
                document.addEventListener('keyup', (e) => {
                    if (this.isExamActive && (e.key === 'PrintScreen' || e.keyCode === 44)) {
                        this.recordViolation('screenshot_attempt');
                        this.showFlash();
                        alert('Screenshots are not allowed during the exam!');
                    }
                });
            },
            
            recordViolation(type) {
                if (!this.isExamActive || this.autoSubmitPending) return;
                
                this.violations++;
                
                // Send violation to server
                if (window.Livewire) {
                    Livewire.dispatch('securityViolation', { type: type, count: this.violations });
                }
                
                // Auto-submit with confirmation
                if (this.violations >= this.maxViolations && !this.autoSubmitPending) {
                    this.autoSubmitPending = true;
                    
                    alert(`Too many security violations (${this.violations}). Your exam will be auto-submitted.`);
                    
                    if (window.Livewire) {
                        Livewire.dispatch('autoSubmitExam', { reason: 'security_violations' });
                    }
                    
                    setTimeout(() => {
                        this.deactivate();
                    }, 1000);
                }
            },
            
            showTabSwitchWarning() {
                const warning = document.getElementById('tabSwitchWarning');
                const count = document.getElementById('violationCount');
                if (warning && count) {
                    count.textContent = this.violations;
                    warning.style.display = 'flex';
                }
            },
            
            showSecurityIndicator(message, level = 'success') {
                const indicator = document.getElementById('securityIndicator');
                const status = document.getElementById('securityStatus');
                
                if (indicator && status) {
                    status.textContent = message;
                    indicator.className = 'security-indicator';
                    
                    if (level === 'warning') {
                        indicator.classList.add('warning');
                    } else if (level === 'danger') {
                        indicator.classList.add('danger');
                    }
                    
                    indicator.style.display = 'flex';
                }
            },
            
            hideSecurityIndicator() {
                const indicator = document.getElementById('securityIndicator');
                if (indicator) {
                    indicator.style.display = 'none';
                }
            },
            
            showFlash() {
                const flash = document.createElement('div');
                flash.className = 'copy-detection-flash';
                document.body.appendChild(flash);
                setTimeout(() => flash.remove(), 500);
            },
            
            requestFullscreen() {
                const elem = document.documentElement;
                if (elem.requestFullscreen) {
                    elem.requestFullscreen().then(() => {
                        this.isFullscreenActive = true;
                    }).catch(() => {
                        this.isFullscreenActive = false;
                    });
                } else if (elem.webkitRequestFullscreen) {
                    elem.webkitRequestFullscreen();
                    this.isFullscreenActive = true;
                } else if (elem.msRequestFullscreen) {
                    elem.msRequestFullscreen();
                    this.isFullscreenActive = true;
                }
                document.body.classList.add('fullscreen-forced');
            },
            
            // FIXED: Safe fullscreen exit
            exitFullscreen() {
                // Only try to exit if we're actually in fullscreen
                if (document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement) {
                    try {
                        if (document.exitFullscreen) {
                            document.exitFullscreen().catch(() => {
                                // Silent fail - document may not be active
                            });
                        } else if (document.webkitExitFullscreen) {
                            document.webkitExitFullscreen();
                        } else if (document.msExitFullscreen) {
                            document.msExitFullscreen();
                        }
                    } catch (error) {
                        // Silent fail - fullscreen already exited
                    }
                }
                document.body.classList.remove('fullscreen-forced');
                this.isFullscreenActive = false;
            },
            
            preventDevTools() {
                const devtoolsOpen = false;
                const element = new Image();
                Object.defineProperty(element, 'id', {
                    get: () => {
                        if (this.isExamActive) {
                            this.recordViolation('devtools_open');
                        }
                    }
                });
                
                setInterval(() => {
                    devtoolsOpen && element;
                }, 1000);
            }
        };
        
        // Initialize security on page load
        document.addEventListener('DOMContentLoaded', () => {
            ExamSecurity.init();
        });
        
        // Global functions
        window.ExamSecurity = ExamSecurity;
        
        function hideTabWarning() {
            const warning = document.getElementById('tabSwitchWarning');
            if (warning) {
                warning.style.display = 'none';
            }
        }
        
        // Listen for Livewire events
        document.addEventListener('livewire:init', () => {
            Livewire.on('startTimer', () => {
                ExamSecurity.activate();
            });
            
            Livewire.on('examCompleted', () => {
                ExamSecurity.deactivate();
            });
            
            Livewire.on('allowFullscreenExit', () => {
                ExamSecurity.deactivate();
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>