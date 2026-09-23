@extends('layouts.app')
@section('title', 'Low Stock')

@section('content')
<div>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Low Stock Ingredients</h1>

    <!-- Red Banner -->
    <div id="restock-banner" class="hidden mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded font-semibold text-sm"></div>

    <!-- Table Container -->
    <div class="bg-white border border-slate-200 rounded shadow-sm overflow-x-auto">
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr>
                    <th class="text-left border-b p-2 bg-slate-100">Name</th>
                    <th class="text-left border-b p-2 bg-slate-100">Unit</th>
                    <th class="text-left border-b p-2 bg-slate-100">Current Stock</th>
                    <th class="text-left border-b p-2 bg-slate-100">Reorder Level</th>
                    <th class="text-left border-b p-2 bg-slate-100">Shortage</th>
                </tr>
            </thead>
            <tbody id="low-stock-tbody">
                <tr><td colspan="5" class="border-b p-4 text-center text-slate-500">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const banner = document.getElementById('restock-banner');
    const tbody = document.getElementById('low-stock-tbody');

    fetch(`${window.API_BASE}/ingredients/low-stock`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            const items = Array.isArray(data) ? data : (data.data || []);
            
            if (items.length > 0) {
                banner.textContent = `⚠️ ${items.length} ingredient(s) need restocking`;
                banner.classList.remove('hidden');
            } else {
                banner.classList.add('hidden');
            }

            if (items.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="border-b p-4 text-center text-slate-500">No low-stock items 🎉</td></tr>';
                return;
            }

            tbody.innerHTML = '';
            items.forEach(item => {
                const tr = document.createElement('tr');
                tr.className = 'bg-red-50';

                const stock = Number(item.current_stock || 0);
                const reorder = Number(item.reorder_level || 0);
                const shortage = Math.max(0, reorder - stock);

                tr.innerHTML = `
                    <td class="border-b p-2 font-semibold text-slate-900">${item.name}</td>
                    <td class="border-b p-2">${item.unit}</td>
                    <td class="border-b p-2 text-red-600 font-bold">${stock}</td>
                    <td class="border-b p-2">${reorder}</td>
                    <td class="border-b p-2 font-semibold text-red-700">${shortage}</td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(err => {
            tbody.innerHTML = `<tr><td colspan="5" class="border-b p-4 text-center text-red-600">Error: ${err.message}</td></tr>`;
        });
});
</script>
@endsection
