<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'OrderNow')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col font-sans">
    <nav class="bg-slate-900 text-white px-6 py-4 flex items-center gap-6">
        <a href="{{ url('/') }}" class="font-bold text-xl tracking-tight text-white mr-2">OrderNow</a>
        <a href="{{ url('/') }}" class="px-3 py-1.5 rounded transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/50' }}">Dashboard</a>
        <a href="{{ url('/menu') }}" class="px-3 py-1.5 rounded transition-colors {{ request()->routeIs('menu.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/50' }}">Menu</a>
        <a href="{{ url('/ingredients') }}" class="px-3 py-1.5 rounded transition-colors {{ request()->routeIs('ingredients.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/50' }}">Ingredients</a>
        <a href="{{ url('/orders') }}" class="px-3 py-1.5 rounded transition-colors {{ request()->routeIs('orders.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/50' }}">Orders</a>
        <a href="{{ url('/low-stock') }}" class="px-3 py-1.5 rounded transition-colors {{ request()->routeIs('low-stock') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/50' }}">Low Stock</a>
    </nav>

    <main class="max-w-7xl mx-auto p-6 flex-1 w-full">
        @yield('content')
    </main>

    <footer class="bg-slate-100 border-t border-slate-200 text-slate-500 text-center py-4 text-sm mt-auto">
        &copy; {{ date('Y') }} OrderNow. All rights reserved.
    </footer>

    <script>
      window.API_BASE = '{{ url("/api") }}';
    </script>
</body>
</html>
