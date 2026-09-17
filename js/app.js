document.addEventListener('DOMContentLoaded', () => {
    // Initial fetch
    loadProducts();

    // Setup Event Listeners
    setupFormListeners();
});

// Helper for currency formatting
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount || 0);
}

// Helper for HTML escaping
function escapeHtml(str) {
    if (!str) return '';
    return str.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/**
 * Load and display products via AJAX
 */
function loadProducts() {
    const tableBody = document.getElementById('productsTableBody');
    const grandTotalElement = document.getElementById('grandTotalValue');

    if (!tableBody) return;

    fetch('api.php?action=list')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderTable(data.products);
                if (grandTotalElement) {
                    grandTotalElement.textContent = formatCurrency(data.sum_total);
                }
            } else {
                showAlert('danger', data.error || 'Failed to load products.');
            }
        })
        .catch(err => {
            console.error('Error fetching products:', err);
            showAlert('danger', 'Error connecting to server.');
        });
}

/**
 * Render products table rows
 */
function renderTable(products) {
    const tableBody = document.getElementById('productsTableBody');
    const emptyRow = document.getElementById('emptyRow');

    if (!products || products.length === 0) {
        tableBody.innerHTML = `
            <tr id="emptyRow">
                <td colspan="6" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                    No products submitted yet. Fill out the form above to add your first product.
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    products.forEach(p => {
        const totalVal = (parseFloat(p.quantity_in_stock) * parseFloat(p.price_per_item)).toFixed(2);
        
        html += `
            <tr id="product-row-${p.id}">
                <td class="fw-semibold text-white">
                    ${escapeHtml(p.product_name)}
                </td>
                <td>
                    <span class="badge badge-qty">${p.quantity_in_stock}</span>
                </td>
                <td>
                    <span class="badge badge-price">${formatCurrency(p.price_per_item)}</span>
                </td>
                <td class="text-muted small">
                    <i class="bi bi-clock me-1"></i>${escapeHtml(p.datetime_submitted)}
                </td>
                <td class="total-value-text">
                    ${formatCurrency(totalVal)}
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-info me-1" onclick="prepareEdit('${p.id}', '${escapeHtml(p.product_name)}', ${p.quantity_in_stock}, ${p.price_per_item})">
                        <i class="bi bi-pencil-square me-1"></i>Edit
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct('${p.id}')">
                        <i class="bi bi-trash me-1"></i>Delete
                    </button>
                </td>
            </tr>
        `;
    });

    tableBody.innerHTML = html;
}

/**
 * Form listeners and live calculation
 */
function setupFormListeners() {
    const addForm = document.getElementById('addProductForm');
    const qtyInput = document.getElementById('quantity_in_stock');
    const priceInput = document.getElementById('price_per_item');
    const liveCalc = document.getElementById('liveTotalCalc');

    function updateLiveCalc() {
        const qty = parseFloat(qtyInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        const total = (qty * price).toFixed(2);
        liveCalc.textContent = formatCurrency(total);
    }

    if (qtyInput && priceInput) {
        qtyInput.addEventListener('input', updateLiveCalc);
        priceInput.addEventListener('input', updateLiveCalc);
    }

    if (addForm) {
        addForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const name = document.getElementById('product_name').value.trim();
            const qty = document.getElementById('quantity_in_stock').value;
            const price = document.getElementById('price_per_item').value;
            const submitBtn = document.getElementById('btnSubmit');

            if (!name || qty === '' || price === '') {
                showAlert('warning', 'Please fill in all form fields.');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            fetch('api.php?action=create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    product_name: name,
                    quantity_in_stock: parseInt(qty),
                    price_per_item: parseFloat(price)
                })
            })
                .then(res => res.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add Product';

                    if (data.success) {
                        showAlert('success', data.message || 'Product added successfully!');
                        addForm.reset();
                        updateLiveCalc();
                        renderTable(data.products);
                        document.getElementById('grandTotalValue').textContent = formatCurrency(data.sum_total);
                    } else {
                        showAlert('danger', data.error || 'Failed to add product.');
                    }
                })
                .catch(err => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add Product';
                    console.error('AJAX Error:', err);
                    showAlert('danger', 'An error occurred while submitting.');
                });
        });
    }

    // Edit form submission
    const editForm = document.getElementById('editProductForm');
    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const id = document.getElementById('edit_id').value;
            const name = document.getElementById('edit_product_name').value.trim();
            const qty = document.getElementById('edit_quantity_in_stock').value;
            const price = document.getElementById('edit_price_per_item').value;
            const saveBtn = document.getElementById('btnSaveEdit');

            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

            fetch('api.php?action=update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: id,
                    product_name: name,
                    quantity_in_stock: parseInt(qty),
                    price_per_item: parseFloat(price)
                })
            })
                .then(res => res.json())
                .then(data => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = 'Save Changes';

                    if (data.success) {
                        const editModalEl = document.getElementById('editModal');
                        const modal = bootstrap.Modal.getInstance(editModalEl);
                        if (modal) modal.hide();

                        showAlert('success', data.message || 'Product updated successfully!');
                        renderTable(data.products);
                        document.getElementById('grandTotalValue').textContent = formatCurrency(data.sum_total);
                    } else {
                        showAlert('danger', data.error || 'Failed to update product.');
                    }
                })
                .catch(err => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = 'Save Changes';
                    console.error('AJAX Error:', err);
                    showAlert('danger', 'An error occurred while updating.');
                });
        });
    }
}

/**
 * Open edit modal with prefilled data
 */
function prepareEdit(id, name, qty, price) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_product_name').value = name;
    document.getElementById('edit_quantity_in_stock').value = qty;
    document.getElementById('edit_price_per_item').value = price;

    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    editModal.show();
}

/**
 * Delete product via AJAX
 */
function deleteProduct(id) {
    if (!confirm('Are you sure you want to delete this product item?')) {
        return;
    }

    fetch('api.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message || 'Product deleted.');
                renderTable(data.products);
                document.getElementById('grandTotalValue').textContent = formatCurrency(data.sum_total);
            } else {
                showAlert('danger', data.error || 'Failed to delete product.');
            }
        })
        .catch(err => {
            console.error('AJAX Error:', err);
            showAlert('danger', 'Error deleting product.');
        });
}

/**
 * Toast / Alert alert notification helper
 */
function showAlert(type, message) {
    const alertArea = document.getElementById('alertContainer');
    if (!alertArea) return;

    const alertEl = document.createElement('div');
    alertEl.className = `alert alert-${type} alert-dismissible fade show shadow-sm`;
    alertEl.role = 'alert';
    alertEl.innerHTML = `
        <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'} me-2"></i>
        ${escapeHtml(message)}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    alertArea.appendChild(alertEl);

    // Auto dismiss after 4 seconds
    setTimeout(() => {
        alertEl.classList.remove('show');
        setTimeout(() => alertEl.remove(), 200);
    }, 4000);
}
