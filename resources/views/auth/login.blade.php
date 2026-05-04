<!DOCTYPE html>
<html lang="en" class="h-full bg-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CMMS Aruna Hijau Power</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .login-bg {
            background-image: linear-gradient(rgba(6, 78, 59, 0.7), rgba(6, 78, 59, 0.7)), url('{{ asset('login-bg.png') }}');
            background-size: cover;
            background-position: center;
        }
        .text-brand-green { color: #10b981; }
        .bg-brand-green { background-color: #10b981; }
        .hover-bg-brand-green:hover { background-color: #059669; }
    </style>
</head>
<body class="h-full overflow-hidden">
    <div class="flex h-full">
        <!-- Left Side: Visual -->
        <div class="hidden lg:flex lg:w-1/2 login-bg flex-col justify-between p-12 text-white">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white rounded-xl p-0.5 shadow-lg">
                    <img src="{{ asset('logo.jpeg') }}" alt="Logo" class="w-full h-full object-contain rounded-lg">
                </div>
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">CMMS AHP</h2>
                    <p class="text-xs font-medium text-emerald-300 uppercase tracking-widest">Aruna Hijau Power</p>
                </div>
            </div>
            
            <div class="max-w-md">
                <h1 class="text-5xl font-bold leading-tight mb-6">Sustainable Energy, <span class="text-emerald-400">Smartly Managed.</span></h1>
                <p class="text-lg text-emerald-50">Optimize your renewable energy assets with Aruna's unified maintenance platform.</p>
            </div>

            <div class="text-sm text-emerald-300/60 flex items-center justify-between">
                <span>&copy; {{ date('Y') }} PT Aruna Hijau Power</span>
                <span class="text-[10px] font-bold bg-white/10 px-2 py-0.5 rounded-full uppercase tracking-widest">v{{ config('app.version') }}</span>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
            <div class="w-full max-w-md space-y-8">
                <div class="text-center lg:text-left">
                    <div class="lg:hidden flex justify-center mb-6">
                        <img src="{{ asset('logo.jpeg') }}" alt="Logo" class="w-24 h-24 rounded-2xl shadow-xl border border-gray-100">
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Sign In</h2>
                    <p class="mt-2 text-gray-500">Access the Aruna Hijau Power Maintenance System.</p>
                </div>

                @if (session('status'))
                    <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-4 py-3 rounded-xl text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div class="space-y-1">
                        <label for="email" class="text-sm font-semibold text-gray-700">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                                placeholder="name@arunahijaupower.com">
                        </div>
                        @error('email')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1" x-data="{ show: false }">
                        <div class="flex items-center justify-between">
                            <label for="password" class="text-sm font-semibold text-gray-700">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-500 transition-colors">Forgot password?</a>
                            @endif
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input id="password" x-bind:type="show ? 'text' : 'password'" name="password" required
                                class="block w-full pl-10 pr-10 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                                placeholder="••••••••">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="show" class="h-5 w-5" style="display: none;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-600">Keep me signed in</label>
                    </div>

                    <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all">
                        Sign In
                    </button>
                </form>

                <div class="pt-8 text-center border-t border-gray-100">
                    <p class="text-sm text-gray-500 font-medium italic">Authorized Access Only</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
