@extends('layouts.app')
@section('title', 'Orders')

@section('content')
<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Orders</h1>
        <a href="{{ url('/orders/create') }}" class="px-3 py-1 rounded text-white bg-blue-600 hover:bg-blue-700 text-sm font-medium">
            + New Order
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="flex items-center gap-2 mb-4">
        <span class="text-sm font-semibold text-slate-700 mr-2">Filter:</span>
        <button type="button" data-filter="all" class="filter-btn px-3 py-1 rounded text-xs font-medium bg-blue-600 text-white">All</button>
        <button type="button" data-filter="pending" class="filter-btn px-3 py-1 rounded text-xs font-medium bg-slate-200 text-slate-700 hover:bg-slate-300">Pending</button>
        <button type="button" data-filter="confirmed" class="filter-btn px-3 py-1 rounded text-xs font-medium bg-slate-200 text-slate-700 hover:bg-slate-300">Confirmed</button>
        <button type="button" data-filter="cancelled" class="filter-btn px-3 py-1 rounded text-xs font-medium bg-slate-200 text-slate-700 hover:bg-slate-300">Cancelled</button>
    </div>

    <!-- Error Box -->
    <div id="error-box" class="hidden mb-4 p-3 bg-red-100 border border-red-300 text-red-700 rounded text-sm"></div>

    <!-- Orders Table -->
    <div class="bg-white border border-slate-200 rounded shadow-sm overflow-x-auto mb-4">
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr>
                    <th class="text-left border-b p-2 bg-slate-100">Order #</th>
                    <th class="text-left border-b p-2 bg-slate-100">Status</th>
                    <th class="text-left border-b p-2 bg-slate-100">Total</th>
                    <th class="text-left border-b p-2 bg-slate-100">Items</th>
                    <th class="text-left border-b p-2 bg-slate-100">Created</th>
                    <th class="text-left border-b p-2 bg-slate-100">Actions</th>
                </tr>
            </thead>
            <tbody id="orders-table-body">
                <tr><td colspan="6" class="border-b p-4 text-center text-slate-500">Loading...</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <div class="flex items-center justify-between text-sm text-slate-600" id="pagination-container">
        <span id="page-info">Page 1 of 1</span>
        <div class="flex gap-2">
            <button type="button" id="prev-btn" disabled class="px-3 py-1 rounded border border-slate-300 bg-white text-slate-600 disabled:opacity-50 hover:bg-slate-50">Prev</button>
            <button type="button" id="next-btn" disabled class="px-3 py-1 rounded border border-slate-300 bg-white text-slate-600 disabled:opacity-50 hover:bg-slate-50">Next</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let currentPage = 1;
    let totalPages = 1;
    let currentFilter = 'all';
    let loadedOrders = [];

    const tableBody = document.getElementById('orders-table-body');
    const pageInfo = document.getElementById('page-info');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const errorBox = document.getElementById('error-box');

    function fetchOrders(page = 1) {
        tableBody.innerHTML = '<tr><td colspan="6" class="border-b p-4 text-center text-slate-500">Loading...</td></tr>';
        errorBox.classList.add('hidden');

        fetch(`${window.API_BASE}/orders?page=${page}`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(res => {
                loadedOrders = Array.isArray(res) ? res : (res.data || []);
                const meta = res.meta || {};
                currentPage = meta.current_page || page;
                totalPages = meta.last_page || 1;

                pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
                prevBtn.disabled = currentPage <= 1;
                nextBtn.disabled = currentPage >= totalPages;

                renderOrders();
            })
            .catch(err => {
                tableBody.innerHTML = '<tr><td colspan="6" class="border-b p-4 text-center text-red-600">Failed to load orders.</td></tr>';
                errorBox.textContent = `Error: ${err.message}`;
                errorBox.classList.remove('hidden');
            });
    }

    function renderOrders() {
        const filtered = loadedOrders.filter(order => {
            if (currentFilter === 'all') return true;
            return (order.status || '').toLowerCase() === currentFilter;
        });

        if (filtered.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="6" class="border-b p-4 text-center text-slate-500">No orders found.</td></tr>';
            return;
        }

        tableBody.innerHTML = '';
        filtered.forEach(order => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 cursor-pointer transition-colors';

            let badgeClass = 'bg-slate-100 text-slate-800';
            const st = (order.status || '').toLowerCase();
            if (st === 'pending') badgeClass = 'bg-yellow-100 text-yellow-800';
            else if (st === 'confirmed') badgeClass = 'bg-green-100 text-green-800';
            else if (st === 'cancelled') badgeClass = 'bg-red-100 text-red-800';

            const itemCount = Array.isArray(order.items) 
                ? order.items.reduce((acc, i) => acc + (Number(i.quantity) || 1), 0)
                : (order.item_count || 1);

            const createdDate = order.created_at ? new Date(order.created_at).toLocaleString() : '-';

            tr.innerHTML = `
                <td class="border-b p-2 font-semibold text-slate-900">${order.order_number}</td>
                <td class="border-b p-2">
                    <span class="rounded px-2 py-0.5 text-xs font-medium capitalize ${badgeClass}">
                        ${order.status}
                    </span>
                </td>
                <td class="border-b p-2 font-medium">RM ${Number(order.total || 0).toFixed(2)}</td>
                <td class="border-b p-2">${itemCount}</td>
                <td class="border-b p-2 text-slate-500">${createdDate}</td>
                <td class="border-b p-2">
                    <a href="${window.location.origin}/orders/${order.id}" class="px-3 py-1 rounded text-white bg-blue-600 hover:bg-blue-700 text-xs inline-block">
                        View
                    </a>
                </td>
            `;

            tr.addEventListener('click', (e) => {
                if (e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON') {
                    window.location.href = `/orders/${order.id}`;
                }
            });

            tableBody.appendChild(tr);
        });
    }

    // Filter Buttons Listener
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filter-btn').forEach(b => {
                b.className = 'filter-btn px-3 py-1 rounded text-xs font-medium bg-slate-200 text-slate-700 hover:bg-slate-300';
            });
            btn.className = 'filter-btn px-3 py-1 rounded text-xs font-medium bg-blue-600 text-white';
            currentFilter = btn.dataset.filter;
            renderOrders();
        });
    });

    prevBtn.addEventListener('click', () => {
        if (currentPage > 1) fetchOrders(currentPage - 1);
    });

    nextBtn.addEventListener('click', () => {
        if (currentPage < totalPages) fetchOrders(currentPage + 1);
    });

    fetchOrders(1);
});
</script>
@endsection
