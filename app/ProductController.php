<?php

require_once __DIR__ . '/ProductStorage.php';

class ProductController
{
    private $storage;

    public function __construct()
    {
        $this->storage = new ProductStorage();
    }

    /**
     * Handle incoming API requests based on action
     */
    public function handleRequest()
    {
        header('Content-Type: application/json; charset=utf-8');

        $action = $_GET['action'] ?? $_POST['action'] ?? 'list';

        // Parse JSON request body if posted as application/json
        $rawInput = file_get_contents('php://input');
        if (!empty($rawInput)) {
            $jsonInput = json_decode($rawInput, true);
            if (is_array($jsonInput)) {
                $_POST = array_merge($_POST, $jsonInput);
                if (isset($jsonInput['action'])) {
                    $action = $jsonInput['action'];
                }
            }
        }

        try {
            switch ($action) {
                case 'list':
                    $this->listProducts();
                    break;
                case 'create':
                case 'add':
                    $this->createProduct();
                    break;
                case 'update':
                case 'edit':
                    $this->updateProduct();
                    break;
                case 'delete':
                    $this->deleteProduct();
                    break;
                default:
                    $this->jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function listProducts()
    {
        $products = $this->storage->getAll();
        $sumTotal = $this->storage->getSumTotal();

        $this->jsonResponse([
            'success' => true,
            'products' => $products,
            'sum_total' => $sumTotal,
            'count' => count($products)
        ]);
    }

    private function createProduct()
    {
        $name = trim($_POST['product_name'] ?? '');
        $quantity = $_POST['quantity_in_stock'] ?? null;
        $price = $_POST['price_per_item'] ?? null;

        // Validation
        if (empty($name)) {
            $this->jsonResponse(['success' => false, 'error' => 'Product name is required.'], 422);
            return;
        }

        if (!is_numeric($quantity) || (int)$quantity < 0) {
            $this->jsonResponse(['success' => false, 'error' => 'Quantity in stock must be a non-negative number.'], 422);
            return;
        }

        if (!is_numeric($price) || (float)$price < 0) {
            $this->jsonResponse(['success' => false, 'error' => 'Price per item must be a non-negative number.'], 422);
            return;
        }

        $newProduct = $this->storage->add($name, (int)$quantity, (float)$price);
        $sumTotal = $this->storage->getSumTotal();
        $allProducts = $this->storage->getAll();

        $this->jsonResponse([
            'success' => true,
            'message' => 'Product submitted successfully!',
            'product' => $newProduct,
            'products' => $allProducts,
            'sum_total' => $sumTotal
        ]);
    }

    private function updateProduct()
    {
        $id = trim($_POST['id'] ?? '');
        $name = trim($_POST['product_name'] ?? '');
        $quantity = $_POST['quantity_in_stock'] ?? null;
        $price = $_POST['price_per_item'] ?? null;

        if (empty($id)) {
            $this->jsonResponse(['success' => false, 'error' => 'Product ID is required for editing.'], 422);
            return;
        }

        if (empty($name)) {
            $this->jsonResponse(['success' => false, 'error' => 'Product name is required.'], 422);
            return;
        }

        if (!is_numeric($quantity) || (int)$quantity < 0) {
            $this->jsonResponse(['success' => false, 'error' => 'Quantity in stock must be a non-negative number.'], 422);
            return;
        }

        if (!is_numeric($price) || (float)$price < 0) {
            $this->jsonResponse(['success' => false, 'error' => 'Price per item must be a non-negative number.'], 422);
            return;
        }

        $updatedProduct = $this->storage->update($id, $name, (int)$quantity, (float)$price);

        if ($updatedProduct === null) {
            $this->jsonResponse(['success' => false, 'error' => 'Product not found.'], 404);
            return;
        }

        $sumTotal = $this->storage->getSumTotal();
        $allProducts = $this->storage->getAll();

        $this->jsonResponse([
            'success' => true,
            'message' => 'Product updated successfully!',
            'product' => $updatedProduct,
            'products' => $allProducts,
            'sum_total' => $sumTotal
        ]);
    }

    private function deleteProduct()
    {
        $id = trim($_POST['id'] ?? '');

        if (empty($id)) {
            $this->jsonResponse(['success' => false, 'error' => 'Product ID is required.'], 422);
            return;
        }

        $this->storage->delete($id);
        $sumTotal = $this->storage->getSumTotal();
        $allProducts = $this->storage->getAll();

        $this->jsonResponse([
            'success' => true,
            'message' => 'Product deleted successfully!',
            'products' => $allProducts,
            'sum_total' => $sumTotal
        ]);
    }

    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
