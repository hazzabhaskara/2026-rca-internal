<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo e(config('app.name', 'Laravel')); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <?php
        // Cache-busting: ambil timestamp file jika ada, fallback ke time()
        if (!function_exists('ver')) {
            function ver($path)
            {
                $fullPath = public_path($path);
                return file_exists($fullPath) ? filemtime($fullPath) : time();
            }
        }
    ?>

    <!-- Favicon ICO -->
    <link rel="icon" type="image/x-icon"
        href="<?php echo e(asset('image/favicon/favicon.ico')); ?>?v=<?php echo e(ver('image/favicon/favicon.ico')); ?>">
    <link rel="shortcut icon" type="image/x-icon"
        href="<?php echo e(asset('image/favicon/favicon.ico')); ?>?v=<?php echo e(ver('image/favicon/favicon.ico')); ?>">

    <!-- Favicon PNG -->
    <link rel="icon" type="image/png" sizes="32x32"
        href="<?php echo e(asset('image/favicon/favicon-32x32.png')); ?>?v=<?php echo e(ver('image/favicon/favicon-32x32.png')); ?>">
    <link rel="icon" type="image/png" sizes="16x16"
        href="<?php echo e(asset('image/favicon/favicon-16x16.png')); ?>?v=<?php echo e(ver('image/favicon/favicon-16x16.png')); ?>">

    <!-- Optional: SVG modern browser -->
    <link rel="icon" type="image/svg+xml"
        href="<?php echo e(asset('image/favicon/favicon.svg')); ?>?v=<?php echo e(ver('image/favicon/favicon.svg')); ?>">


    
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
    <script src="https://cdn.tailwindcss.com"></script>

    
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="//unpkg.com/alpinejs" defer></script>


    
    <style>
        .smooth-transition {
            transition: all 0.3s ease-in-out;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05),
                0 4px 6px -2px rgba(0, 0, 0, 0.025);
        }

        main::-webkit-scrollbar-thumb {
            background-color: purple;
        }

        main::-webkit-scrollbar-thumb:hover {
            background-color: purple;
        }

        main {
            scrollbar-color: #f3f4f6;
        }

        .btn-primary {
            background-color: blue;
            color: white;
        }

        .bg-primary {
            background-color: white;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body class="m-0 p-0 font-sans antialiased bg-white text-gray-800 min-h-screen overflow-y-auto">

    
    <?php echo $__env->make('components.navbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <main class="m-0 p-0 pb-16">
        <?php echo $__env->yieldContent('content'); ?>
    </main>


    <?php
        $themeColor = \App\Setting::get('theme_color', '#4F46E5');
        $textFooter = \App\Setting::get('text_footer', 'ANTS LITE+ ©2025_AN NAML CORP.');
    ?>
    <footer class="bg-[<?php echo e($themeColor); ?>] text-white text-xs fixed bottom-0 left-0 right-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-2 flex justify-between">
            <span><?php echo e($textFooter); ?></span>
            <span></span>
        </div>
    </footer>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    
    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobile-menu');
            const backdrop = document.getElementById('mobile-sidebar-backdrop');

            if (mobileMenu && backdrop) {
                mobileMenu.classList.toggle('hidden');
                mobileMenu.classList.toggle('transform');
                mobileMenu.classList.toggle('translate-x-full');
                backdrop.classList.toggle('hidden');
                document.body.classList.toggle('overflow-hidden',
                    !mobileMenu.classList.contains('hidden'));
            }
        }

        // Simulate loading overlay
        document.addEventListener('DOMContentLoaded', () => {
            const loadingOverlay = document.getElementById('loading-overlay');
            if (loadingOverlay) {
                loadingOverlay.classList.remove('hidden');
                setTimeout(() => {
                    loadingOverlay.classList.add('hidden');
                }, 1000);
            }
        });

        // Turbo Smooth Scroll
        document.addEventListener('turbo:render', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Toggle Sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('hidden');
            }
        }
    </script>

    
    <script>
        <?php if(session('success')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?php echo e(session('success')); ?>',
                confirmButtonText: 'OK'
            });
        <?php elseif(session('error')): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                html: `<?php echo session('error'); ?>`,
                confirmButtonText: 'OK'
            });
        <?php endif; ?>
    </script>

    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Data ini tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>

    
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH C:\laragon\www\2026-rca-internal\resources\views/layouts/app.blade.php ENDPATH**/ ?>