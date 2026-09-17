<?php
require_once __DIR__ . '/app/ProductStorage.php';

$storage = new ProductStorage();
$initialProducts = $storage->getAll();
$initialSumTotal = $storage->getSumTotal();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Inventory Manager - PHP & AJAX Skills Test</title>

    <!-- Twitter Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

    <!-- Header Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 border-bottom border-secondary">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="bi bi-box-seam me-2 text-primary fs-4"></i>
                Product Inventory Manager
            </a>
            <span class="badge bg-primary px-3 py-2 fs-6">Laravel & PHP Skills Test</span>
        </div>
    </nav>

    <div class="container">
        <!-- Alert Notification Area -->
        <div id="alertContainer" class="mb-3"></div>

        <!-- Form Card -->
        <div class="card main-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0 text-white fw-bold">
                    <i class="bi bi-plus-circle-fill me-2 text-primary"></i>Add New Product
                </h5>
                <span class="live-calc-badge">
                    Estimated Total: <span id="liveTotalCalc" class="fw-bold text-white">$0.00</span>
                </span>
            </div>
            <div class="card-body p-4">
                <form id="addProductForm">
                    <div class="row g-3">
                        <!-- Product Name -->
                        <div class="col-md-5">
                            <label for="product_name" class="form-label text-light fw-semibold">Product Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-tag"></i></span>
                                <input type="text" class="form-control" id="product_name" name="product_name" placeholder="e.g. Ergonomic Office Chair" required>
                            </div>
                        </div>

                        <!-- Quantity in Stock -->
                        <div class="col-md-3">
                            <label for="quantity_in_stock" class="form-label text-light fw-semibold">Quantity in Stock</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-layers"></i></span>
                                <input type="number" class="form-control" id="quantity_in_stock" name="quantity_in_stock" min="0" step="1" placeholder="e.g. 10" required>
                            </div>
                        </div>

                        <!-- Price Per Item -->
                        <div class="col-md-4">
                            <label for="price_per_item" class="form-label text-light fw-semibold">Price Per Item ($)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-currency-dollar"></i></span>
                                <input type="number" class="form-control" id="price_per_item" name="price_per_item" min="0" step="0.01" placeholder="e.g. 199.99" required>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" id="btnSubmit" class="btn btn-primary px-4">
                            <i class="bi bi-plus-circle me-2"></i>Add Product
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Products Table Card -->
        <div class="card main-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0 text-white fw-bold">
                    <i class="bi bi-list-columns-reverse me-2 text-info"></i>Submitted Products Inventory
                </h5>
                <span class="text-muted small">Ordered by Datetime Submitted</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Product Name</th>
                                <th scope="col">Quantity in Stock</th>
                                <th scope="col">Price Per Item</th>
                                <th scope="col">Datetime Submitted</th>
                                <th scope="col">Total Value Number</th>
                                <th scope="col" style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="productsTableBody">
                            <?php if (empty($initialProducts)): ?>
                                <tr id="emptyRow">
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        No products submitted yet. Fill out the form above to add your first product.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($initialProducts as $p): ?>
                                    <?php 
                                        $totalVal = number_format(($p['quantity_in_stock'] * $p['price_per_item']), 2, '.', '');
                                    ?>
                                    <tr id="product-row-<?= htmlspecialchars($p['id']) ?>">
                                        <td class="fw-semibold text-white">
                                            <?= htmlspecialchars($p['product_name']) ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-qty"><?= (int)$p['quantity_in_stock'] ?></span>
                                        </td>
                                        <td>
                                            <span class="badge badge-price">$<?= number_format($p['price_per_item'], 2) ?></span>
                                        </td>
                                        <td class="text-muted small">
                                            <i class="bi bi-clock me-1"></i><?= htmlspecialchars($p['datetime_submitted']) ?>
                                        </td>
                                        <td class="total-value-text">
                                            $<?= number_format($totalVal, 2) ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-info me-1" onclick="prepareEdit('<?= htmlspecialchars($p['id']) ?>', '<?= htmlspecialchars(addslashes($p['product_name'])) ?>', <?= (int)$p['quantity_in_stock'] ?>, <?= (float)$p['price_per_item'] ?>)">
                                                <i class="bi bi-pencil-square me-1"></i>Edit
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct('<?= htmlspecialchars($p['id']) ?>')">
                                                <i class="bi bi-trash me-1"></i>Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end fw-bold text-uppercase text-light pe-3">
                                    Sum Total of All Total Values:
                                </td>
                                <td class="grand-total-text" id="grandTotalValue">
                                    $<?= number_format($initialSumTotal, 2) ?>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Extra Credit: Edit Product Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-white" id="editModalLabel">
                        <i class="bi bi-pencil-square me-2 text-info"></i>Edit Product Row
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editProductForm">
                    <div class="modal-body">
                        <input type="hidden" id="edit_id" name="id">
                        
                        <div class="mb-3">
                            <label for="edit_product_name" class="form-label text-light fw-semibold">Product Name</label>
                            <input type="text" class="form-control" id="edit_product_name" name="product_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_quantity_in_stock" class="form-label text-light fw-semibold">Quantity in Stock</label>
                            <input type="number" class="form-control" id="edit_quantity_in_stock" name="quantity_in_stock" min="0" step="1" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_price_per_item" class="form-label text-light fw-semibold">Price Per Item ($)</label>
                            <input type="number" class="form-control" id="edit_price_per_item" name="price_per_item" min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="btnSaveEdit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Twitter Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript App -->
    <script src="js/app.js"></script>
</body>
</html>
