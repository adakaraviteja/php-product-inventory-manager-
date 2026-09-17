<?php

class ProductStorage
{
    private $filePath;

    public function __construct($filePath = null)
    {
        if ($filePath === null) {
            $this->filePath = __DIR__ . '/../data/products.json';
        } else {
            $this->filePath = $filePath;
        }

        // Ensure directory exists
        $dir = dirname($this->filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Ensure file exists with valid JSON array
        if (!file_exists($this->filePath) || filesize($this->filePath) === 0) {
            file_put_contents($this->filePath, json_encode([], JSON_PRETTY_PRINT));
        }
    }

    /**
     * Read all products from the JSON file
     */
    public function getAll()
    {
        if (!file_exists($this->filePath)) {
            return [];
        }

        $json = file_get_contents($this->filePath);
        $data = json_decode($json, true);

        if (!is_array($data)) {
            $data = [];
        }

        // Sort by datetime_submitted (newest first)
        usort($data, function ($a, $b) {
            return strtotime($b['datetime_submitted'] ?? 0) - strtotime($a['datetime_submitted'] ?? 0);
        });

        return $data;
    }

    /**
     * Add a new product to the JSON file
     */
    public function add($productName, $quantity, $price)
    {
        $products = $this->getAll();

        $quantity = (int) $quantity;
        $price = (float) $price;
        $totalValue = round($quantity * $price, 2);

        $newProduct = [
            'id' => 'prod_' . uniqid(),
            'product_name' => trim($productName),
            'quantity_in_stock' => $quantity,
            'price_per_item' => $price,
            'total_value' => $totalValue,
            'datetime_submitted' => date('Y-m-d H:i:s')
        ];

        // Prepend new product
        array_unshift($products, $newProduct);

        $this->saveAll($products);

        return $newProduct;
    }

    /**
     * Update an existing product by ID
     */
    public function update($id, $productName, $quantity, $price)
    {
        $products = $this->getAll();
        $updatedProduct = null;

        $quantity = (int) $quantity;
        $price = (float) $price;
        $totalValue = round($quantity * $price, 2);

        foreach ($products as &$product) {
            if ($product['id'] === $id) {
                $product['product_name'] = trim($productName);
                $product['quantity_in_stock'] = $quantity;
                $product['price_per_item'] = $price;
                $product['total_value'] = $totalValue;
                // Preserve original datetime_submitted or update if needed
                $updatedProduct = $product;
                break;
            }
        }

        if ($updatedProduct !== null) {
            $this->saveAll($products);
        }

        return $updatedProduct;
    }

    /**
     * Delete a product by ID
     */
    public function delete($id)
    {
        $products = $this->getAll();
        $filtered = array_filter($products, function ($p) use ($id) {
            return $p['id'] !== $id;
        });

        $this->saveAll(array_values($filtered));
        return true;
    }

    /**
     * Calculate sum total of all total values
     */
    public function getSumTotal()
    {
        $products = $this->getAll();
        $sum = 0.0;
        foreach ($products as $p) {
            $sum += (float) ($p['total_value'] ?? 0);
        }
        return round($sum, 2);
    }

    /**
     * Save products array back to JSON file
     */
    private function saveAll(array $products)
    {
        file_put_contents(
            $this->filePath,
            json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
