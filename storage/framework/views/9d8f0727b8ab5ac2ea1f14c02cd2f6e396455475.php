

<?php $__env->startSection('content'); ?>
    <div class="min-h-screen flex flex-col justify-center items-center bg-gray-100">
        
        <div class="mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-32 h-32 text-red-500 animate-bounce" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728" />
            </svg>
        </div>

        
        <h1 class="text-4xl font-bold text-gray-800 mb-4">Oops! Terjadi Kesalahan Server</h1>

        
        <p class="text-lg text-gray-600 mb-8 text-center max-w-md">
            Silakan coba beberapa saat lagi.
        </p>

        
        <a href="<?php echo e(url('/')); ?>"
            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow transition duration-300">
            Kembali ke Beranda
        </a>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\2026-rca-internal\resources\views/errors/505.blade.php ENDPATH**/ ?>