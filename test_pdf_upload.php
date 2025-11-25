<?php
// Test script for PDF upload functionality

// Include the InvoiceController class
require_once 'backend/app/Http/Controllers/Api/InvoiceController.php';

// Read the test invoice text
$text = file_get_contents('test_pdfs/sample_invoice.txt');

// Create a mock controller to test the extraction
class TestInvoiceController extends App\Http\Controllers\Api\InvoiceController {
    public function testExtractInvoiceDataFromText($text) {
        return $this->extractInvoiceDataFromText($text);
    }
}

// Create an instance of the test controller
$controller = new TestInvoiceController();

// Call the method
$extractedData = $controller->testExtractInvoiceDataFromText($text);

// Display results
echo "Extracted Data:\n";
echo "===============\n";
echo "Invoice Number: " . ($extractedData['invoice_number'] ?? 'N/A') . "\n";
echo "Amount: " . ($extractedData['amount'] ?? 'N/A') . "\n";
echo "Invoice Date: " . ($extractedData['invoice_date'] ?? 'N/A') . "\n";
echo "Due Date: " . ($extractedData['due_date'] ?? 'N/A') . "\n";
echo "Vendor: " . ($extractedData['vendor'] ?? 'N/A') . "\n";
echo "Bill To: " . ($extractedData['bill_to'] ?? 'N/A') . "\n";
echo "Payment Terms: " . ($extractedData['payment_terms'] ?? 'N/A') . "\n";
echo "Notes: " . ($extractedData['notes'] ?? 'N/A') . "\n";

echo "\nSerials:\n";
if (isset($extractedData['serials']) && is_array($extractedData['serials'])) {
    foreach ($extractedData['serials'] as $serial) {
        echo "  - $serial\n";
    }
}

echo "\nLine Items:\n";
if (isset($extractedData['line_items']) && is_array($extractedData['line_items'])) {
    foreach ($extractedData['line_items'] as $index => $item) {
        echo "Item " . ($index + 1) . ":\n";
        echo "  Description: " . ($item['description'] ?? 'N/A') . "\n";
        echo "  Size: " . ($item['size'] ?? 'N/A') . "\n";
        echo "  Serial: " . ($item['serial'] ?? 'N/A') . "\n";
        echo "  Quantity: " . ($item['quantity'] ?? 'N/A') . "\n";
        echo "  Amount: " . ($item['amount'] ?? 'N/A') . "\n";
        echo "\n";
    }
}

echo "Total line items extracted: " . (isset($extractedData['line_items']) ? count($extractedData['line_items']) : 0) . "\n";