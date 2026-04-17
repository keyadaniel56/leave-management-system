<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <!-- Toast Notification -->
        <div id="toast"
            class="hidden fixed bottom-5 right-5 z-50 max-w-sm bg-white border border-gray-200 shadow-lg rounded-lg p-4 text-sm text-gray-800 transition-all">
            <p id="toast-message"></p>
        </div>

        <script>
            @auth
            window.Laravel = {
                userId: {{ auth()->id() }},
                userRole: '{{ auth()->user()->role }}'
            };

            function showToast(message, color = 'green') {
                const toast = document.getElementById('toast');
                const msg   = document.getElementById('toast-message');
                msg.textContent = message;
                toast.className = `fixed bottom-5 right-5 z-50 max-w-sm bg-white border-l-4 border-${color}-500 shadow-lg rounded-lg p-4 text-sm text-gray-800`;
                setTimeout(() => toast.classList.add('hidden'), 5000);
            }

            document.addEventListener('DOMContentLoaded', () => {
                // Admin: listen for new leave submissions
                if (window.Laravel.userRole === 'admin') {
                    window.Echo.channel('admin.notifications')
                        .listen('.leave.submitted', (e) => {
                            showToast(e.message, 'yellow');
                        });
                }

                // Employee: listen for their leave being reviewed
                if (window.Laravel.userRole === 'employee') {
                    window.Echo.channel('employee.' + window.Laravel.userId)
                        .listen('.leave.reviewed', (e) => {
                            const color = e.status === 'approved' ? 'green' : 'red';
                            showToast(e.message, color);
                        });
                }
            });
            @endauth
        </script>
    </body>
</html>
