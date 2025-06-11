@extends('layouts.guest')

@section('title', 'Login')

@section('body')
    <x-partials.authentication-card>
        <form action="{{ route('login') }}" method="POST" class="px-3 md:p-5 w-full border-b-2" x-data="{ 
            showPassword: false,
            emailFocused: false,
            passwordFocused: false,
            togglePasswordVisibility() {
                this.showPassword = !this.showPassword;
            }
        }">
            <!-- Email Field -->
            <div class="mb-6 relative group transition-all duration-200">
                <x-input name="email" id="email" type="email" label="Email" 
                    x-on:focus="emailFocused = true"
                    x-on:blur="emailFocused = false"
                    x-bind:class="{'ring-2 ring-blue-500 border-blue-300': emailFocused}" />
                <div class="absolute right-3 top-10 transition-transform duration-200" 
                     x-show="emailFocused"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>

            <!-- Password Field with Eye Toggle -->
            <div class="mb-6 relative group transition-all duration-200">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <input x-bind:type="showPassword ? 'text' : 'password'" 
                           name="password" 
                           id="password" 
                           required
                           x-on:focus="passwordFocused = true"
                           x-on:blur="passwordFocused = false"
                           x-bind:class="{'ring-2 ring-blue-500 border-blue-300': passwordFocused}"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm pr-10">
                    
                    <!-- Eye Toggle Button -->
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <button type="button" 
                                x-on:click="togglePasswordVisibility()"
                                class="text-gray-400 hover:text-blue-500 focus:outline-none transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 x-bind:class="{'text-blue-500': showPassword}">
                                <path x-show="!showPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path x-show="!showPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                <path x-show="showPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Focus Indicator -->
                    <div class="absolute right-10 top-2 transition-all duration-200" 
                         x-show="passwordFocused"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                </div>
                
                <!-- Password Visibility Indicator -->
                <div x-show="showPassword" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="mt-1 text-xs text-blue-500 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Password is visible
                </div>
            </div>

            <!-- Remember Me -->
            <div class="my-3 flex items-center">
                <input type="checkbox" id="remember" name="remember" 
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition-all duration-200">
                <label for="remember" class="ml-2 block text-sm text-gray-700">Remember Me</label>
            </div>

            @csrf
            <x-button class="my-3 px-6 md:px-10 w-full transform hover:scale-[1.01] transition-all duration-200 active:scale-[0.99]">
                Log in
            </x-button>
        </form>

        <div class="py-6 text-center">
            <p class="text-gray-600">Don't have an account? 
                <a href="{{route('register')}}" class="text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200 hover:underline" aria-label="Register">
                    Create Account
                </a>
            </p>
        </div>

        <x-slot:footer>
            <a href="{{route('password.request')}}" 
               class="text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200 hover:underline" 
               aria-label="Forgot Password">
                Forgot your Password?
            </a>
        </x-slot:footer>
    </x-partials.authentication-card>
@endsection