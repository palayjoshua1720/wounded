<?php
// Test script for invoice extraction logic

// Include the InvoiceController class
require_once 'backend/app/Http/Controllers/Api/InvoiceController.php';

// Read the test invoice text
$text = file_get_contents('test_invoice.txt');

// Create an instance of the controller
$controller = new App\Http\Controllers\Api\InvoiceController();

// Use reflection to access the private method
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('extractLineItemsFromTable');
$method->setAccessible(true);

// Prepare line items array
$lineItems = [];

// Display the text we're working with
echo "Input text:\n";
echo "==========\n";
echo substr($text, 0, 500) . "...\n\n";

// Call the method
$method->invokeArgs($controller, [$text, &$lineItems]);

// Display results
echo "Extracted Line Items:\n";
echo "====================\n";
foreach ($lineItems as $index => $item) {
    echo "Item " . ($index + 1) . ":\n";
    echo "  Description: " . $item['description'] . "\n";
    echo "  Size: " . $item['size'] . "\n";
    echo "  Serial: " . $item['serial'] . "\n";
    echo "  Quantity: " . $item['quantity'] . "\n";
    echo "  Amount: " . $item['amount'] . "\n";
    echo "\n";
}

echo "Total items extracted: " . count($lineItems) . "\n";