@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>

    <!-- Loading State -->
    <div id="loading-state" class="p-8 text-center text-slate-500 font-medium bg-white rounded-lg shadow-sm border border-slate-200">
        Loading...
    </div>

    <!-- Error State -->
    <div id="error-state" class="hidden p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
        Failed to load dashboard data. Please try refreshing.
    </div>

    <!-- Dashboard Content -->
    <div id="dashboard-content" class="hidden space-y-6">
        <!-- Row 1: 4 Stat Cards -->
        <div class="grid grid-cols-4 gap-4">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
                <div class="text-sm font-medium text-slate-500">Today's Sales (RM)</div>
                <div id="stat-sales" class="text-3xl font-bold text-slate-900 mt-2">RM 0.00</div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
                <div class="text-sm font-medium text-slate-500">Orders Today</div>
                <div id="stat-orders" class="text-3xl font-bold text-slate-900 mt-2">0</div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
                <div class="text-sm font-medium text-slate-500">Confirmed</div>
                <div id="stat-confirmed" class="text-3xl font-bold text-emerald-600 mt-2">0</div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
                <div class="text-sm font-medium text-slate-500">Pending</div>
                <div id="stat-pending" class="text-3xl font-bold text-amber-600 mt-2">0</div>
            </div>
        </div>

        <!-- Row 2: Low Stock & Top Selling -->
        <div class="grid grid-cols-2 gap-6">
            <!-- Left: Low Stock Ingredients -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Low Stock Ingredients</h2>
                <div id="low-stock-container"></div>
            </div>

            <!-- Right: Top Selling Today -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Top Selling Today</h2>
                <div id="top-selling-container"></div>
            </div>
        </div>

        <!-- Row 3: Recent Orders -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Recent Orders</h2>
            <div id="recent-orders-container"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const loadingState = document.getElementById('loading-state');
    const errorState = document.getElementById('error-state');
    const dashboardContent = document.getElementById('dashboard-content');

    fetch(`${window.API_BASE}/dashboard`, { 
        headers: { 'Accept': 'application/json' } 
    })
    .then(r => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
    })
    .then(data => {
        // Row 1: Stat Cards
        const today = data.today || {};
        const sales = today.sales != null ? Number(today.sales).toFixed(2) : '0.00';
        document.getElementById('stat-sales').textContent = `RM ${sales}`;
        document.getElementById('stat-orders').textContent = today.order_count ?? 0;
        document.getElementById('stat-confirmed').textContent = today.confirmed_count ?? 0;
        document.getElementById('stat-pending').textContent = today.pending_count ?? 0;

        // Row 2 Left: Low Stock Ingredients
        const lowStockContainer = document.getElementById('low-stock-container');
        const lowStockItems = data.low_stock_ingredients || [];
        if (lowStockItems.length === 0) {
            lowStockContainer.innerHTML = '<p class="text-slate-500 text-sm py-2">No low-stock items 🎉</p>';
        } else {
            let html = `
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-3">Name</th>
                                <th class="py-2.5 px-3">Current Stock</th>
                                <th class="py-2.5 px-3">Reorder Level</th>
                                <th class="py-2.5 px-3">Unit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
            `;
            lowStockItems.forEach(item => {
                const isBelow = Number(item.current_stock) < Number(item.reorder_level);
                const stockColor = isBelow ? 'text-red-600 font-semibold' : '';
                html += `
                    <tr>
                        <td class="py-2.5 px-3 font-medium text-slate-900">${item.name}</td>
                        <td class="py-2.5 px-3 ${stockColor}">${item.current_stock}</td>
                        <td class="py-2.5 px-3">${item.reorder_level}</td>
                        <td class="py-2.5 px-3">${item.unit}</td>
                    </tr>
                `;
            });
            html += `</tbody></table></div>`;
            lowStockContainer.innerHTML = html;
        }

        // Row 2 Right: Top Selling Today
        const topSellingContainer = document.getElementById('top-selling-container');
        const topSellingItems = data.top_selling_today || [];
        if (topSellingItems.length === 0) {
            topSellingContainer.innerHTML = '<p class="text-slate-500 text-sm py-2">No sales yet today</p>';
        } else {
            let html = `<ul class="divide-y divide-slate-100">`;
            topSellingItems.forEach(item => {
                html += `
                    <li class="py-3 flex justify-between items-center text-sm">
                        <span class="font-medium text-slate-800">${item.name}</span>
                        <span class="bg-slate-100 text-slate-700 font-semibold px-2.5 py-1 rounded-full text-xs">${item.total_qty} sold</span>
                    </li>
                `;
            });
            html += `</ul>`;
            topSellingContainer.innerHTML = html;
        }

        // Row 3: Recent Orders Table
        const recentOrdersContainer = document.getElementById('recent-orders-container');
        const recentOrders = data.recent_orders || [];
        if (recentOrders.length === 0) {
            recentOrdersContainer.innerHTML = '<p class="text-slate-500 text-sm py-2">No recent orders found.</p>';
        } else {
            let html = `
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-3">Order #</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3">Total</th>
                                <th class="py-2.5 px-3">Items</th>
                                <th class="py-2.5 px-3">Created At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
            `;
            recentOrders.forEach(order => {
                let badgeClass = 'bg-slate-100 text-slate-800';
                const st = (order.status || '').toLowerCase();
                if (st === 'pending') badgeClass = 'bg-amber-100 text-amber-800';
                else if (st === 'confirmed') badgeClass = 'bg-emerald-100 text-emerald-800';
                else if (st === 'cancelled') badgeClass = 'bg-red-100 text-red-800';

                const dateStr = order.created_at ? new Date(order.created_at).toLocaleString() : '';
                const totalStr = `RM ${Number(order.total || 0).toFixed(2)}`;

                html += `
                    <tr class="hover:bg-slate-50 cursor-pointer transition-colors" onclick="window.location.href='/orders/${order.id}'">
                        <td class="py-2.5 px-3 font-semibold text-slate-900">${order.order_number}</td>
                        <td class="py-2.5 px-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize ${badgeClass}">
                                ${order.status}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 font-medium text-slate-900">${totalStr}</td>
                        <td class="py-2.5 px-3">${order.item_count}</td>
                        <td class="py-2.5 px-3 text-slate-500">${dateStr}</td>
                    </tr>
                `;
            });
            html += `</tbody></table></div>`;
            recentOrdersContainer.innerHTML = html;
        }

        loadingState.classList.add('hidden');
        dashboardContent.classList.remove('hidden');
    })
    .catch(err => {
        console.error(err);
        loadingState.classList.add('hidden');
        errorState.classList.remove('hidden');
        errorState.textContent = `Error loading data: ${err.message || 'Unknown error'}`;
    });
});
</script>
@endsection
