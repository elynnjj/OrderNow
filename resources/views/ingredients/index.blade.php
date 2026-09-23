@extends('layouts.app')
@section('title', 'Ingredients')

@section('content')
<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Ingredients</h1>
        <button id="add-ingredient-btn" type="button" class="px-3 py-1 rounded text-white bg-blue-600 hover:bg-blue-700">
            + Add Ingredient
        </button>
    </div>

    <!-- Error Alert Box -->
    <div id="error-box" class="hidden mb-4 p-3 bg-red-100 border border-red-300 text-red-700 rounded text-sm"></div>

    <!-- Create Form (Hidden by default) -->
    <div id="form-container" class="hidden mb-6 p-4 bg-white border border-slate-200 rounded shadow-sm">
        <h2 class="text-lg font-semibold mb-4 text-slate-800">Add Ingredient</h2>
        <form id="ingredient-form" onsubmit="return false;">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Name</label>
                    <input type="text" id="field-name" required class="w-full border border-slate-300 rounded p-2 text-sm focus:outline-none focus:border-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Unit</label>
                    <select id="field-unit" required class="w-full border border-slate-300 rounded p-2 text-sm focus:outline-none focus:border-blue-500">
                        <option value="g">g</option>
                        <option value="kg">kg</option>
                        <option value="ml">ml</option>
                        <option value="l">l</option>
                        <option value="pcs">pcs</option>
                        <option value="slice">slice</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Current Stock</label>
                    <input type="number" step="any" min="0" id="field-stock" required class="w-full border border-slate-300 rounded p-2 text-sm focus:outline-none focus:border-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Reorder Level</label>
                    <input type="number" step="any" min="0" id="field-reorder" required class="w-full border border-slate-300 rounded p-2 text-sm focus:outline-none focus:border-blue-500" />
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" id="save-ingredient-btn" class="px-3 py-1 rounded text-white bg-blue-600 hover:bg-blue-700">Save</button>
                <button type="button" id="cancel-ingredient-btn" class="px-3 py-1 rounded text-slate-700 bg-slate-200 hover:bg-slate-300">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Table Container -->
    <div class="bg-white border border-slate-200 rounded shadow-sm overflow-x-auto">
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr>
                    <th class="text-left border-b p-2 bg-slate-100">ID</th>
                    <th class="text-left border-b p-2 bg-slate-100">Name</th>
                    <th class="text-left border-b p-2 bg-slate-100">Unit</th>
                    <th class="text-left border-b p-2 bg-slate-100">Current Stock</th>
                    <th class="text-left border-b p-2 bg-slate-100">Reorder Level</th>
                    <th class="text-left border-b p-2 bg-slate-100">Status</th>
                    <th class="text-left border-b p-2 bg-slate-100">Actions</th>
                </tr>
            </thead>
            <tbody id="ingredients-table-body">
                <tr><td colspan="7" class="border-b p-4 text-center text-slate-500">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const errorBox = document.getElementById('error-box');
    const formContainer = document.getElementById('form-container');
    const nameInput = document.getElementById('field-name');
    const unitSelect = document.getElementById('field-unit');
    const stockInput = document.getElementById('field-stock');
    const reorderInput = document.getElementById('field-reorder');
    const tableBody = document.getElementById('ingredients-table-body');

    function showError(msg) {
        errorBox.innerHTML = msg;
        errorBox.classList.remove('hidden');
    }

    function hideError() {
        errorBox.innerHTML = '';
        errorBox.classList.add('hidden');
    }

    function showForm() {
        hideError();
        nameInput.value = '';
        unitSelect.value = 'g';
        stockInput.value = '';
        reorderInput.value = '';
        formContainer.classList.remove('hidden');
    }

    function hideForm() {
        formContainer.classList.add('hidden');
        hideError();
    }

    function loadIngredients() {
        fetch(`${window.API_BASE}/ingredients`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                const items = Array.isArray(data) ? data : (data.data || []);
                if (items.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="7" class="border-b p-4 text-center text-slate-500">No ingredients found.</td></tr>';
                    return;
                }

                tableBody.innerHTML = '';
                items.forEach(item => {
                    const tr = document.createElement('tr');
                    const isLow = Number(item.current_stock) <= Number(item.reorder_level);
                    if (isLow) {
                        tr.className = 'bg-red-50';
                    }

                    const statusBadge = isLow 
                        ? '<span class="px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-800">Low</span>' 
                        : '<span class="px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800">OK</span>';

                    tr.innerHTML = `
                        <td class="border-b p-2 font-medium">${item.id}</td>
                        <td class="border-b p-2 font-semibold text-slate-900">${item.name}</td>
                        <td class="border-b p-2">${item.unit}</td>
                        <td class="border-b p-2 ${isLow ? 'text-red-600 font-semibold' : ''}">${item.current_stock}</td>
                        <td class="border-b p-2">${item.reorder_level}</td>
                        <td class="border-b p-2">${statusBadge}</td>
                        <td class="border-b p-2">
                            <button type="button" class="adjust-btn px-3 py-1 rounded text-white bg-blue-600 hover:bg-blue-700 text-xs">+/-</button>
                        </td>
                    `;

                    tr.querySelector('.adjust-btn').addEventListener('click', () => adjustStock(item));
                    tableBody.appendChild(tr);
                });
            })
            .catch(err => {
                tableBody.innerHTML = `<tr><td colspan="7" class="border-b p-4 text-center text-red-600">Error loading ingredients: ${err.message}</td></tr>`;
            });
    }

    function saveIngredient() {
        hideError();
        const name = nameInput.value.trim();
        const unit = unitSelect.value;
        const current_stock = parseFloat(stockInput.value);
        const reorder_level = parseFloat(reorderInput.value);

        const payload = { name, unit, current_stock, reorder_level };

        fetch(`${window.API_BASE}/ingredients`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(async r => {
            const resData = await r.json().catch(() => ({}));
            if (!r.ok) {
                if (r.status === 422 && resData.errors) {
                    const errMsgs = Object.values(resData.errors).flat().join('<br>');
                    showError(errMsgs);
                } else {
                    showError(resData.message || `Request failed with status ${r.status}`);
                }
                return;
            }
            hideForm();
            loadIngredients();
        })
        .catch(err => {
            showError(`Error saving ingredient: ${err.message}`);
        });
    }

    function adjustStock(ingredient) {
        const changeStr = window.prompt("Enter change amount (e.g. 100 or -50):");
        if (changeStr === null || changeStr.trim() === '') return;

        const change = parseFloat(changeStr);
        if (isNaN(change) || change === 0) {
            alert('Invalid change amount.');
            return;
        }

        const reason = window.prompt("Reason (adjustment/waste/restock):", "adjustment");
        if (!reason || reason.trim() === '') return;

        fetch(`${window.API_BASE}/ingredients/${ingredient.id}/adjust`, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ change, reason: reason.trim() })
        })
        .then(async r => {
            const resData = await r.json().catch(() => ({}));
            if (!r.ok) {
                if (r.status === 422 && resData.errors) {
                    const errMsgs = Object.values(resData.errors).flat().join('\n');
                    alert(`Adjustment failed:\n${errMsgs}`);
                } else {
                    alert(resData.message || `Adjustment failed with status ${r.status}`);
                }
                return;
            }
            loadIngredients();
        })
        .catch(err => {
            alert(`Error adjusting stock: ${err.message}`);
        });
    }

    document.getElementById('add-ingredient-btn').addEventListener('click', () => {
        if (formContainer.classList.contains('hidden')) {
            showForm();
        } else {
            hideForm();
        }
    });

    document.getElementById('save-ingredient-btn').addEventListener('click', saveIngredient);
    document.getElementById('cancel-ingredient-btn').addEventListener('click', hideForm);

    loadIngredients();
});
</script>
@endsection
