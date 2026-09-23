@extends('layouts.app')
@section('title', 'New Order')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-900">New Order</h1>
        <a href="{{ url('/orders') }}" class="text-sm text-slate-600 hover:text-slate-900">&larr; Cancel</a>
    </div>

    <!-- Error Box -->
    <div id="error-box" class="hidden mb-4 p-3 bg-red-100 border border-red-300 text-red-700 rounded text-sm"></div>

    <div class="bg-white border border-slate-200 rounded shadow-sm p-4">
        <h2 class="text-base font-semibold text-slate-800 mb-3">Order Items</h2>
        
        <table class="w-full text-sm border-collapse mb-4">
            <thead>
                <tr>
                    <th class="text-left border-b p-2 bg-slate-100">Menu Item</th>
                    <th class="text-left border-b p-2 bg-slate-100" style="width: 120px;">Unit Price</th>
                    <th class="text-left border-b p-2 bg-slate-100" style="width: 100px;">Quantity</th>
                    <th class="text-left border-b p-2 bg-slate-100" style="width: 120px;">Subtotal</th>
                    <th class="text-left border-b p-2 bg-slate-100" style="width: 80px;">Action</th>
                </tr>
            </thead>
            <tbody id="cart-tbody"></tbody>
            <tfoot>
                <tr class="border-t border-slate-200">
                    <td colspan="3" class="p-3 text-right font-bold text-slate-800">Grand Total:</td>
                    <td colspan="2" class="p-3 font-bold text-lg text-slate-900" id="grand-total">RM 0.00</td>
                </tr>
            </tfoot>
        </table>

        <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm p-3 rounded mb-4">
          Orders are auto-confirmed when stock is available. If stock is 
          insufficient, the order will be saved as <strong>pending</strong> and 
          can be confirmed manually once ingredients are restocked.
        </div>

        <div class="flex items-center justify-between border-t border-slate-100 pt-4">
            <button type="button" id="add-cart-row-btn" class="text-xs px-3 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium">
                + Add Item
            </button>
            <div class="flex gap-2">
                <a href="{{ url('/orders') }}" class="px-3 py-1 rounded text-slate-700 bg-slate-200 hover:bg-slate-300 text-sm font-medium">Cancel</a>
                <button type="button" id="submit-order-btn" class="px-3 py-1 rounded text-white bg-blue-600 hover:bg-blue-700 text-sm font-medium">Create Order</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let activeMenuItems = [];
    const cartTbody = document.getElementById('cart-tbody');
    const grandTotalEl = document.getElementById('grand-total');
    const errorBox = document.getElementById('error-box');

    function showError(msg) {
        errorBox.innerHTML = msg;
        errorBox.classList.remove('hidden');
    }

    function hideError() {
        errorBox.innerHTML = '';
        errorBox.classList.add('hidden');
    }

    function loadMenuItems() {
        return fetch(`${window.API_BASE}/menu-items`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                const items = Array.isArray(data) ? data : (data.data || []);
                activeMenuItems = items.filter(i => i.is_active);
                addCartRow();
            })
            .catch(err => {
                showError(`Failed to load menu items: ${err.message}`);
            });
    }

    function addCartRow() {
        const tr = document.createElement('tr');
        tr.className = 'cart-row';

        let options = activeMenuItems.map(item => `
            <option value="${item.id}" data-price="${item.price}">${item.name} (RM ${Number(item.price).toFixed(2)})</option>
        `).join('');

        tr.innerHTML = `
            <td class="border-b p-2">
                <select class="item-select w-full border border-slate-300 rounded p-1 text-sm">
                    <option value="">-- Select Item --</option>
                    ${options}
                </select>
            </td>
            <td class="border-b p-2 font-medium item-price">RM 0.00</td>
            <td class="border-b p-2">
                <input type="number" min="1" value="1" class="item-qty w-full border border-slate-300 rounded p-1 text-sm" />
            </td>
            <td class="border-b p-2 font-semibold item-subtotal text-slate-900">RM 0.00</td>
            <td class="border-b p-2">
                <button type="button" class="remove-row-btn px-2 py-1 text-xs rounded bg-red-600 text-white hover:bg-red-700">Remove</button>
            </td>
        `;

        const select = tr.querySelector('.item-select');
        const qtyInput = tr.querySelector('.item-qty');
        const removeBtn = tr.querySelector('.remove-row-btn');

        select.addEventListener('change', () => updateTotals());
        qtyInput.addEventListener('input', () => updateTotals());
        removeBtn.addEventListener('click', () => {
            tr.remove();
            updateTotals();
        });

        cartTbody.appendChild(tr);
        updateTotals();
    }

    function updateTotals() {
        let grandTotal = 0;
        const rows = cartTbody.querySelectorAll('.cart-row');

        rows.forEach(row => {
            const select = row.querySelector('.item-select');
            const qtyInput = row.querySelector('.item-qty');
            const priceTd = row.querySelector('.item-price');
            const subtotalTd = row.querySelector('.item-subtotal');

            const selectedOption = select.options[select.selectedIndex];
            const price = selectedOption && selectedOption.dataset.price ? parseFloat(selectedOption.dataset.price) : 0;
            const qty = parseInt(qtyInput.value) || 0;
            const subtotal = price * qty;

            priceTd.textContent = `RM ${price.toFixed(2)}`;
            subtotalTd.textContent = `RM ${subtotal.toFixed(2)}`;

            grandTotal += subtotal;
        });

        grandTotalEl.textContent = `RM ${grandTotal.toFixed(2)}`;
    }

    function submitOrder() {
        hideError();
        const rows = cartTbody.querySelectorAll('.cart-row');
        const items = [];

        rows.forEach(row => {
            const menuItemId = row.querySelector('.item-select').value;
            const qty = parseInt(row.querySelector('.item-qty').value);
            if (menuItemId && qty > 0) {
                items.push({ menu_item_id: parseInt(menuItemId), quantity: qty });
            }
        });

        if (items.length === 0) {
            showError('Please select at least one menu item with a valid quantity.');
            return;
        }

        fetch(`${window.API_BASE}/orders`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ items })
        })
        .then(async r => {
            const data = await r.json().catch(() => ({}));
            if (!r.ok) {
                if (r.status === 422 && data.errors) {
                    const errMsgs = Object.values(data.errors).flat().join('<br>');
                    showError(errMsgs);
                } else {
                    showError(data.message || `Failed to create order (Status ${r.status})`);
                }
                return;
            }
            const orderObj = data.data || data;
            const orderId = orderObj.id;
            window.location.href = `/orders/${orderId}`;
        })
        .catch(err => {
            showError(`Error creating order: ${err.message}`);
        });
    }

    document.getElementById('add-cart-row-btn').addEventListener('click', addCartRow);
    document.getElementById('submit-order-btn').addEventListener('click', submitOrder);

    loadMenuItems();
});
</script>
@endsection
