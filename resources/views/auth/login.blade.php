<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Keuangan</title>
    <link rel="shortcut icon" href="{{ asset('image/favicon/favicon.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    @php
        $themeColor = \App\Setting::get('theme_color', '#4F46E5');
        $secondaryColor = \App\Setting::get('theme_secondary_color', '#6366f1');
    @endphp

    <style>
        :root {
            --primary-color: {{ $themeColor }};
            --secondary-color: {{ $secondaryColor }};
        }

        .bg-primary {
            background-color: var(--primary-color);
        }

        .bg-secondary {
            background-color: var(--secondary-color);
        }

        .text-secondary {
            color: var(--secondary-color);
        }

        .border-secondary {
            border-color: var(--secondary-color);
        }

        .hover\:bg-secondary:hover {
            background-color: var(--secondary-color);
        }

        .smooth-transition {
            transition: all 0.3s ease;
        }

        .error-shake {
            animation: shake 0.5s cubic-bezier(.36, .07, .19, .97) both;
        }

        @keyframes shake {

            10%,
            90% {
                transform: translateX(-1px);
            }

            20%,
            80% {
                transform: translateX(2px);
            }

            30%,
            50%,
            70% {
                transform: translateX(-3px);
            }

            40%,
            60% {
                transform: translateX(3px);
            }
        }
    </style>
</head>

<body class="from-white h-screen overflow-hidden font-sans">


    <!-- Card utama -->
    <div class="flex h-screen">
        <!-- Kiri: Form Login -->
        <div class="w-full md:w-1/2 flex flex-col justify-center items-center p-8 bg-white">
            <div class="w-full max-w-sm">
                <div class="text-center mb-8">
                    <div class="mx-auto w-52 h-26 rounded-full overflow-hidden flex items-center justify-center mb-3">
                        <img src="{{ asset('image/logologinpage.jpg') }}" class="h-full w-full object-contain"
                            alt="Logo">
                    </div>
                    <h4 class="text-lg font-bold text-gray-800">Welcome Back!</h4>
                    <p class="text-gray-500 text-sm">Please login to continue</p>
                </div>

                {{-- Flash messages --}}
                @if (session('status'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded-lg">
                        <i class="bi bi-check-circle-fill mr-2"></i> {{ session('status') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded-lg">
                        <i class="bi bi-exclamation-triangle-fill mr-2"></i> {{ session('error') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded-lg">
                        @foreach ($errors->all() as $error)
                            <p><i class="bi bi-exclamation-circle-fill mr-2"></i> {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 text-secondary">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:border-secondary focus:ring-2 focus:ring-secondary placeholder-gray-400"
                            placeholder="Username">
                    </div>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 text-secondary">
                            <i class="bi bi-lock-fill"></i>
                        </div>
                        <input type="password" id="password" name="password" required
                            class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:border-secondary focus:ring-2 focus:ring-secondary placeholder-gray-400"
                            placeholder="Password">
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center text-sm text-gray-600">
                            <input id="remember_me" name="remember" type="checkbox"
                                class="h-4 w-4 text-secondary border-gray-300 rounded mr-2">
                            Remember me
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full bg-secondary text-white py-3 rounded-lg hover:bg-opacity-90 shadow-md flex items-center justify-center">
                        <i class="bi bi-box-arrow-in-right mr-2"></i> Sign In
                    </button>
                </form>
            </div>
        </div>

        <!-- Kanan: Gambar -->

        <!-- Kanan: Gambar -->
        <div class="hidden md:block md:w-1/2 flex items-center justify-center">
            <img src="{{ asset('image/page_login_rca.jpg') }}?v=1" alt="Login Banner"
                class="w-full h-full object-contain">
        </div>

    </div>


    <script>
        // Smooth animations for error messages
        document.addEventListener('DOMContentLoaded', function() {
            // Animate error messages
            const errorMessage = document.getElementById('errorMessage');
            const validationErrors = document.getElementById('validationErrors');
            const statusMessage = document.getElementById('statusMessage');

            if (errorMessage) {
                errorMessage.classList.add('error-shake');
                setTimeout(() => {
                    errorMessage.classList.remove('opacity-0');
                    errorMessage.classList.add('opacity-100');
                }, 100);
            }

            if (validationErrors) {
                validationErrors.classList.add('error-shake');
                setTimeout(() => {
                    validationErrors.classList.remove('opacity-0');
                    validationErrors.classList.add('opacity-100');
                }, 100);
            }

            if (statusMessage) {
                setTimeout(() => {
                    statusMessage.classList.remove('opacity-0');
                    statusMessage.classList.add('opacity-100');
                }, 300);
            }

            // Form validation
            document.querySelector('form').addEventListener('submit', function(e) {
                const name = document.getElementById('name').value;
                const password = document.getElementById('password').value;

                if (!name || !password) {
                    e.preventDefault();
                    const errorDiv = document.createElement('div');
                    errorDiv.className =
                        'bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded-lg error-shake';
                    errorDiv.innerHTML =
                        '<i class="bi bi-exclamation-triangle-fill mr-2"></i> Please fill in all fields';

                    const form = document.querySelector('form');
                    form.insertBefore(errorDiv, form.firstChild);

                    setTimeout(() => {
                        errorDiv.classList.add('opacity-100');
                    }, 100);
                }
            });
        });
    </script>
</body>

</html>
