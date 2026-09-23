@extends('layouts.app')
@section('title', 'Order Detail')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Top row -->
    <div class="mb-4">
        <a href="{{ url('/orders') }}" class="text-blue-600 hover:underline inline-block font-medium">&larr; Back to Orders</a>
    </div>

    <!-- Error alert container -->
    <div id="error-alert" class="hidden mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded shadow-sm">
        <strong id="error-message" class="block font-bold mb-1"></strong>
        <div id="error-details" class="text-sm mt-1"></div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded shadow p-6 mb-6">
        <div class="flex items-center justify-between border-b border-slate-200 pb-4 mb-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">
                    Order <span id="order-number"></span>
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Created: <span id="created-at"></span>
                </p>
            </div>
            <div>
                <span id="status-badge" class="rounded px-3 py-1 text-xs font-semibold capitalize"></span>
            </div>
        </div>

        <!-- Info note container -->
        <div id="status-note" class="hidden mb-6 p-3 rounded text-sm font-medium"></div>

        <!-- Items Table -->
        <div class="mb-6">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr>
                        <th class="text-left border-b p-2 bg-slate-100">Menu Item</th>
                        <th class="text-left border-b p-2 bg-slate-100" style="width: 120px;">Unit Price</th>
                        <th class="text-left border-b p-2 bg-slate-100" style="width: 90px;">Quantity</th>
                        <th class="text-left border-b p-2 bg-slate-100" style="width: 120px;">Subtotal</th>
                    </tr>
                </thead>
                <tbody id="items-body">
                    <tr><td colspan="4" class="border-b p-4 text-center text-slate-500">Loading...</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Grand Total -->
        <div class="text-right text-2xl font-bold text-slate-900 mb-6">
            Total: RM <span id="grand-total">0.00</span>
        </div>

        <!-- Action buttons container -->
        <div id="actions" class="hidden border-t border-slate-200 pt-4 text-right">
            <button id="confirm-btn" type="button" class="bg-green-600 text-white px-4 py-2 rounded font-medium hover:bg-green-700">
                Confirm Order
            </button>
            <button id="cancel-btn" type="button" class="bg-red-600 text-white px-4 py-2 rounded ml-2 font-medium hover:bg-red-700">
                Cancel Order
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const orderId = {{ $orderId }};

    async function loadOrder() {
        try {
            const res = await fetch(`${window.API_BASE}/orders/${orderId}`, {
                headers: { 'Accept': 'application/json' }
            });
            const json = await res.json();
            const o = json.data || json;

            // Render order number
            document.getElementById('order-number').textContent = o.order_number || '';

            // Render status badge
            const badge = document.getElementById('status-badge');
            badge.textContent = o.status || '';
            const st = (o.status || '').toLowerCase();
            if (st === 'pending') {
                badge.className = 'rounded px-3 py-1 text-xs font-semibold uppercase bg-yellow-100 text-yellow-800';
            } else if (st === 'confirmed') {
                badge.className = 'rounded px-3 py-1 text-xs font-semibold uppercase bg-green-100 text-green-800';
            } else if (st === 'cancelled') {
                badge.className = 'rounded px-3 py-1 text-xs font-semibold uppercase bg-red-100 text-red-800';
            } else {
                badge.className = 'rounded px-3 py-1 text-xs font-semibold uppercase bg-slate-100 text-slate-800';
            }

            // Render created at
            document.getElementById('created-at').textContent = o.created_at 
                ? new Date(o.created_at).toLocaleString() 
                : '';

            // Render items table
            const tbody = document.getElementById('items-body');
            tbody.innerHTML = '';
            const items = o.items || [];
            items.forEach(item => {
                const tr = document.createElement('tr');
                const itemName = item.menu_item_name || (item.menu_item ? item.menu_item.name : `Item #${item.menu_item_id}`);
                const unitPriceFormatted = 'RM ' + Number(item.unit_price || 0).toFixed(2);
                const subtotalFormatted = 'RM ' + Number(item.subtotal || 0).toFixed(2);

                tr.innerHTML = `
                    <td class="border-b p-2 font-medium text-slate-900">${itemName}</td>
                    <td class="border-b p-2">${unitPriceFormatted}</td>
                    <td class="border-b p-2">${item.quantity}</td>
                    <td class="border-b p-2 font-semibold text-slate-900">${subtotalFormatted}</td>
                `;
                tbody.appendChild(tr);
            });

            // Render grand total
            document.getElementById('grand-total').textContent = Number(o.total || 0).toFixed(2);

            // Toggle actions container
            const actions = document.getElementById('actions');
            if (st === 'pending') {
                actions.classList.remove('hidden');
            } else {
                actions.classList.add('hidden');
            }

            // Toggle status note
            const note = document.getElementById('status-note');
            if (st === 'pending') {
                note.innerHTML = 'This order is pending because some ingredients were out of stock at creation time. Restock the missing ingredients, then click <strong>Confirm Order</strong>.';
                note.className = 'mb-6 p-3 rounded text-sm font-medium bg-yellow-50 text-yellow-800 border border-yellow-200';
                note.classList.remove('hidden');
            } else if (st === 'confirmed') {
                note.innerHTML = '✓ This order has been confirmed and inventory has been deducted.';
                note.className = 'mb-6 p-3 rounded text-sm font-medium bg-green-50 text-green-800 border border-green-200';
                note.classList.remove('hidden');
            } else if (st === 'cancelled') {
                note.innerHTML = 'This order was cancelled.';
                note.className = 'mb-6 p-3 rounded text-sm font-medium bg-slate-100 text-slate-700 border border-slate-200';
                note.classList.remove('hidden');
            } else {
                note.classList.add('hidden');
            }
        } catch (err) {
            console.error('Failed to load order:', err);
        }
    }

    // Confirm Button Handler
    document.getElementById('confirm-btn').addEventListener('click', async () => {
        try {
            const res = await fetch(`${window.API_BASE}/orders/${orderId}/confirm`, {
                method: 'POST',
                headers: { 'Accept': 'application/json' }
            });
            if (res.ok) {
                document.getElementById('error-alert').classList.add('hidden');
                await loadOrder();
            } else if (res.status === 422) {
                const err = await res.json();
                document.getElementById('error-message').textContent = err.message || 'Insufficient stock';
                const d = err.details || {};

                if (Array.isArray(d)) {
                    document.getElementById('error-details').innerHTML = d.map(item => 
                        `Required: ${item.required} ${item.unit}<br>Available: ${item.available} ${item.unit}`
                    ).join('<br><br>');
                } else if (d.required !== undefined) {
                    document.getElementById('error-details').innerHTML = `Required: ${d.required} ${d.unit}<br>Available: ${d.available} ${d.unit}`;
                } else {
                    document.getElementById('error-details').innerHTML = '';
                }

                document.getElementById('error-alert').classList.remove('hidden');
            }
        } catch (err) {
            console.error('Error confirming order:', err);
        }
    });

    // Cancel Button Handler
    document.getElementById('cancel-btn').addEventListener('click', async () => {
        if (!confirm('Cancel this order?')) return;
        try {
            await fetch(`${window.API_BASE}/orders/${orderId}/cancel`, {
                method: 'POST',
                headers: { 'Accept': 'application/json' }
            });
            await loadOrder();
        } catch (err) {
            console.error('Error cancelling order:', err);
        }
    });

    loadOrder();
});
</script>
@endsection
