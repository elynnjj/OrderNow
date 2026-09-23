@extends('layouts.app')
@section('title', 'Menu Items')

@section('content')
<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Menu Items</h1>
        <button id="add-menu-btn" type="button" class="px-3 py-1 rounded text-white bg-blue-600 hover:bg-blue-700">
            + Add Item
        </button>
    </div>

    <!-- Error Alert Box -->
    <div id="error-box" class="hidden mb-4 p-3 bg-red-100 border border-red-300 text-red-700 rounded text-sm"></div>

    <!-- Form Container (Hidden initially) -->
    <div id="form-container" class="hidden mb-6 p-4 bg-white border border-slate-200 rounded shadow-sm">
        <h2 id="form-title" class="text-lg font-semibold mb-4 text-slate-800">Add Menu Item</h2>
        <form id="menu-form" onsubmit="return false;">
            <input type="hidden" id="item-id" value="" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Name</label>
                    <input type="text" id="field-name" required class="w-full border border-slate-300 rounded p-2 text-sm focus:outline-none focus:border-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Price (RM)</label>
                    <input type="number" step="0.01" min="0" id="field-price" required class="w-full border border-slate-300 rounded p-2 text-sm focus:outline-none focus:border-blue-500" />
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Description</label>
                <input type="text" id="field-description" class="w-full border border-slate-300 rounded p-2 text-sm focus:outline-none focus:border-blue-500" />
            </div>
            <div class="mb-4 flex items-center gap-2">
                <input type="checkbox" id="field-is-active" checked class="rounded text-blue-600 h-4 w-4" />
                <label for="field-is-active" class="text-sm text-slate-700 font-medium">Active</label>
            </div>

            <!-- Recipe Section -->
            <div class="mb-4 border-t border-slate-200 pt-4">
                <h3 class="text-sm font-semibold text-slate-800 mb-2">Recipe Ingredients</h3>
                <table class="w-full text-sm border-collapse mb-2" id="recipe-table">
                    <thead>
                        <tr>
                            <th class="text-left border-b p-2 bg-slate-100">Ingredient</th>
                            <th class="text-left border-b p-2 bg-slate-100">Quantity</th>
                            <th class="text-left border-b p-2 bg-slate-100" style="width: 90px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="recipe-tbody"></tbody>
                </table>
                <button type="button" id="add-recipe-row-btn" class="text-xs px-2 py-1 rounded bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium">
                    + Add Ingredient
                </button>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" id="save-menu-btn" class="px-3 py-1 rounded text-white bg-blue-600 hover:bg-blue-700">Save</button>
                <button type="button" id="cancel-menu-btn" class="px-3 py-1 rounded text-slate-700 bg-slate-200 hover:bg-slate-300">Cancel</button>
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
                    <th class="text-left border-b p-2 bg-slate-100">Description</th>
                    <th class="text-left border-b p-2 bg-slate-100">Price</th>
                    <th class="text-left border-b p-2 bg-slate-100">Ingredients</th>
                    <th class="text-left border-b p-2 bg-slate-100">Active</th>
                    <th class="text-left border-b p-2 bg-slate-100">Actions</th>
                </tr>
            </thead>
            <tbody id="menu-table-body">
                <tr><td colspan="7" class="border-b p-4 text-center text-slate-500">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let ingredientsCache = [];

    const errorBox = document.getElementById('error-box');
    const formContainer = document.getElementById('form-container');
    const formTitle = document.getElementById('form-title');
    const itemIdInput = document.getElementById('item-id');
    const nameInput = document.getElementById('field-name');
    const priceInput = document.getElementById('field-price');
    const descInput = document.getElementById('field-description');
    const activeCheckbox = document.getElementById('field-is-active');
    const recipeTbody = document.getElementById('recipe-tbody');
    const menuTableBody = document.getElementById('menu-table-body');

    function showError(msg) {
        errorBox.innerHTML = msg;
        errorBox.classList.remove('hidden');
    }

    function hideError() {
        errorBox.innerHTML = '';
        errorBox.classList.add('hidden');
    }

    function loadIngredients() {
        return fetch(`${window.API_BASE}/ingredients`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                ingredientsCache = Array.isArray(data) ? data : (data.data || []);
            })
            .catch(err => console.error('Error fetching ingredients:', err));
    }

    function addRecipeRow(ingredientId = '', quantity = 1) {
        const tr = document.createElement('tr');
        tr.className = 'recipe-row';
        
        let selectOptions = ingredientsCache.map(ing => {
            const selected = String(ing.id) === String(ingredientId) ? 'selected' : '';
            return `<option value="${ing.id}" ${selected}>${ing.name} (${ing.unit})</option>`;
        }).join('');

        tr.innerHTML = `
            <td class="border-b p-2">
                <select class="recipe-ing-id w-full border border-slate-300 rounded p-1 text-sm">
                    <option value="">-- Select Ingredient --</option>
                    ${selectOptions}
                </select>
            </td>
            <td class="border-b p-2">
                <input type="number" step="any" min="0.01" class="recipe-qty w-full border border-slate-300 rounded p-1 text-sm" value="${quantity}" />
            </td>
            <td class="border-b p-2">
                <button type="button" class="remove-recipe-row-btn px-2 py-1 text-xs rounded bg-red-600 text-white hover:bg-red-700">Remove</button>
            </td>
        `;

        tr.querySelector('.remove-recipe-row-btn').addEventListener('click', () => tr.remove());
        recipeTbody.appendChild(tr);
    }

    function showForm(item = null) {
        hideError();
        if (item) {
            formTitle.textContent = 'Edit Menu Item';
            itemIdInput.value = item.id;
            nameInput.value = item.name || '';
            priceInput.value = item.price != null ? item.price : '';
            descInput.value = item.description || '';
            activeCheckbox.checked = !!item.is_active;
            recipeTbody.innerHTML = '';
            
            const recipeItems = item.ingredients || [];
            if (recipeItems.length > 0) {
                recipeItems.forEach(ing => addRecipeRow(ing.id, ing.quantity));
            } else {
                addRecipeRow();
            }
        } else {
            formTitle.textContent = 'Create Menu Item';
            itemIdInput.value = '';
            nameInput.value = '';
            priceInput.value = '';
            descInput.value = '';
            activeCheckbox.checked = true;
            recipeTbody.innerHTML = '';
            addRecipeRow();
        }
        formContainer.classList.remove('hidden');
    }

    function hideForm() {
        formContainer.classList.add('hidden');
        hideError();
    }

    function loadMenuItems() {
        fetch(`${window.API_BASE}/menu-items`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                const items = Array.isArray(data) ? data : (data.data || []);
                if (items.length === 0) {
                    menuTableBody.innerHTML = '<tr><td colspan="7" class="border-b p-4 text-center text-slate-500">No menu items found.</td></tr>';
                    return;
                }

                menuTableBody.innerHTML = '';
                items.forEach(item => {
                    const tr = document.createElement('tr');
                    const ingNames = (item.ingredients || []).map(i => i.name).join(', ') || '-';
                    const activeBadge = item.is_active 
                        ? '<span class="px-2 py-0.5 rounded text-xs text-white bg-green-600 font-medium">Active</span>' 
                        : '<span class="px-2 py-0.5 rounded text-xs text-white bg-slate-400 font-medium">Inactive</span>';

                    tr.innerHTML = `
                        <td class="border-b p-2 font-medium">${item.id}</td>
                        <td class="border-b p-2 font-semibold text-slate-900">${item.name}</td>
                        <td class="border-b p-2 text-slate-600">${item.description || '-'}</td>
                        <td class="border-b p-2 font-medium">RM ${Number(item.price || 0).toFixed(2)}</td>
                        <td class="border-b p-2 text-slate-600">${ingNames}</td>
                        <td class="border-b p-2">${activeBadge}</td>
                        <td class="border-b p-2 flex gap-1">
                            <button type="button" class="edit-btn px-3 py-1 rounded text-white bg-blue-600 hover:bg-blue-700 text-xs">Edit</button>
                            <button type="button" class="delete-btn px-3 py-1 rounded text-white bg-red-600 hover:bg-red-700 text-xs">Delete</button>
                        </td>
                    `;

                    tr.querySelector('.edit-btn').addEventListener('click', () => showForm(item));
                    tr.querySelector('.delete-btn').addEventListener('click', () => deleteMenuItem(item.id));
                    menuTableBody.appendChild(tr);
                });
            })
            .catch(err => {
                menuTableBody.innerHTML = `<tr><td colspan="7" class="border-b p-4 text-center text-red-600">Error loading items: ${err.message}</td></tr>`;
            });
    }

    function saveMenuItem() {
        hideError();
        const id = itemIdInput.value;
        const name = nameInput.value.trim();
        const price = parseFloat(priceInput.value);
        const description = descInput.value.trim();
        const is_active = activeCheckbox.checked;

        const recipeRows = recipeTbody.querySelectorAll('.recipe-row');
        const ingredients = [];
        recipeRows.forEach(row => {
            const ingId = row.querySelector('.recipe-ing-id').value;
            const qty = parseFloat(row.querySelector('.recipe-qty').value);
            if (ingId && !isNaN(qty) && qty > 0) {
                ingredients.push({ ingredient_id: parseInt(ingId), quantity: qty });
            }
        });

        const payload = { name, price, description, is_active, ingredients };
        const method = id ? 'PUT' : 'POST';
        const url = id ? `${window.API_BASE}/menu-items/${id}` : `${window.API_BASE}/menu-items`;

        fetch(url, {
            method: method,
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
            loadMenuItems();
        })
        .catch(err => {
            showError(`Error saving menu item: ${err.message}`);
        });
    }

    function deleteMenuItem(id) {
        if (!confirm('Are you sure you want to delete this menu item?')) return;
        fetch(`${window.API_BASE}/menu-items/${id}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json' }
        })
        .then(r => {
            if (!r.ok) throw new Error(`HTTP ${r.status}`);
            loadMenuItems();
        })
        .catch(err => {
            showError(`Failed to delete menu item: ${err.message}`);
        });
    }

    document.getElementById('add-menu-btn').addEventListener('click', () => {
        if (formContainer.classList.contains('hidden')) {
            showForm();
        } else {
            hideForm();
        }
    });

    document.getElementById('add-recipe-row-btn').addEventListener('click', () => addRecipeRow());
    document.getElementById('save-menu-btn').addEventListener('click', saveMenuItem);
    document.getElementById('cancel-menu-btn').addEventListener('click', hideForm);

    loadIngredients().then(loadMenuItems);
});
</script>
@endsection
