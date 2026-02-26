<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BillerTracking;
use Illuminate\Http\Request;

class BillerTrackingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $billers = BillerTracking::paginate($perPage);
        return response()->json($billers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'invoice_number' => 'required|string|max:255',
            'service_date' => 'required|date',
            'submission_date' => 'nullable|date',
            'medicare_amount' => 'required|numeric|min:0',
            'provider_amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,submitted,processed,paid,denied',
            'clinician' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);
        
        // Additional validation: submission date should not be before service date
        if (!empty($validated['submission_date']) && !empty($validated['service_date'])) {
            $serviceDate = new \DateTime($validated['service_date']);
            $submissionDate = new \DateTime($validated['submission_date']);
            
            if ($submissionDate < $serviceDate) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'submission_date' => ['Submission date cannot be before service date']
                    ]
                ], 422);
            }
        }
        
        $biller = BillerTracking::create($validated);
        return response()->json($biller, 201);
    }

    /**
     * Get form configuration for dynamic form rendering.
     */
    public function getFormConfig()
    {
        $formFields = [
            [
                'key' => 'patientName',
                'label' => 'Patient Name',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Enter patient name',
                'class' => 'w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
            ],
            [
                'key' => 'invoiceNumber',
                'label' => 'Invoice Number',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Enter invoice number',
                'class' => 'w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
            ],
            [
                'key' => 'serviceDate',
                'label' => 'Service Date',
                'type' => 'date',
                'required' => true,
                'class' => 'w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
            ],
            [
                'key' => 'submissionDate',
                'label' => 'Submission Date',
                'type' => 'date',
                'required' => false,
                'class' => 'w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
            ],
            [
                'key' => 'medicareAmount',
                'label' => 'Medicare Amount ($)',
                'type' => 'number',
                'required' => true,
                'step' => '0.01',
                'min' => '0',
                'placeholder' => '0.00',
                'class' => 'w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
            ],
            [
                'key' => 'providerAmount',
                'label' => 'Provider Amount ($)',
                'type' => 'number',
                'required' => true,
                'step' => '0.01',
                'min' => '0',
                'placeholder' => '0.00',
                'class' => 'w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
            ],
            [
                'key' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'required' => true,
                'placeholder' => 'Select status',
                'class' => 'w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white',
                'options' => [
                    ['value' => 'pending', 'label' => 'Pending'],
                    ['value' => 'submitted', 'label' => 'Submitted'],
                    ['value' => 'processed', 'label' => 'Processed'],
                    ['value' => 'paid', 'label' => 'Paid'],
                    ['value' => 'denied', 'label' => 'Denied']
                ]
            ],
            [
                'key' => 'clinician',
                'label' => 'Clinician',
                'type' => 'text',
                'required' => false,
                'placeholder' => 'Enter clinician name',
                'class' => 'w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
            ],
            [
                'key' => 'notes',
                'label' => 'Notes',
                'type' => 'textarea',
                'required' => false,
                'placeholder' => 'Additional notes...',
                'class' => 'w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white resize-none h-24'
            ]
        ];

        // Get required columns as indexed array
        $requiredColumns = [];
        foreach ($formFields as $field) {
            if ($field['required']) {
                $requiredColumns[] = $field['label'];
            }
        }

        return response()->json([
            'formFields' => $formFields,
            'requiredColumns' => $requiredColumns
        ]);
    }

    /**
     * Bulk import biller tracking records from CSV/upload.
     */
    public function bulkImport(Request $request)
    {
        // Log the incoming request data for debugging
        \Log::info('Biller tracking bulk import request', [
            'data_count' => count($request->data ?? []),
            'first_row' => $request->data[0] ?? null,
            'all_keys' => $request->data ? array_keys($request->data[0] ?? []) : [],
            'raw_request' => $request->all()
        ]);
        
        // Debug: Print all column names received
        if (!empty($request->data)) {
            $firstRowKeys = array_keys($request->data[0]);
            \Log::info('Received column names:', $firstRowKeys);
        }
        
        // Custom validation for numeric fields to handle various formats
        $data = $request->data ?? [];
        $validator = \Validator::make(['data' => $data], [
            'data' => 'required|array',
        ]);
        
        // If basic data validation passes, validate each row individually
        if (!$validator->fails() && !empty($data)) {
            // Check the first row to see what columns exist
            $firstRow = $data[0];
            $availableColumns = array_keys($firstRow);
            
            \Log::info('Available columns in CSV:', $availableColumns);
            
            // Check for required columns with flexible naming
            $requiredColumns = ['Patient Name', 'Invoice Number', 'Service Date', 'Status'];
            $optionalColumns = ['Submission Date', 'Clinician', 'Notes'];
            $amountColumns = ['Medicare Amount', 'Provider Amount', 'Medicare Amount ($)', 'Provider Amount ($)'];
            
            // Check if any of the amount column variants exist
            $medicareAmountCol = null;
            $providerAmountCol = null;
            
            foreach ($amountColumns as $col) {
                if (in_array($col, $availableColumns)) {
                    if (strpos($col, 'Medicare') !== false) {
                        $medicareAmountCol = $col;
                    } elseif (strpos($col, 'Provider') !== false) {
                        $providerAmountCol = $col;
                    }
                }
            }
            
            // Validate each row with flexible column names
            foreach ($data as $index => $row) {
                $errors = [];
                
                // Check required columns
                foreach ($requiredColumns as $reqCol) {
                    if (!array_key_exists($reqCol, $row)) {
                        $errors["data.{$index}.{$reqCol}"] = ["The data.{$index}.{$reqCol} field is required."];
                    }
                }
                
                // Check amount columns
                if ($medicareAmountCol && !array_key_exists($medicareAmountCol, $row)) {
                    $errors["data.{$index}.{$medicareAmountCol}"] = ["The data.{$index}.{$medicareAmountCol} field is required."];
                } elseif (!$medicareAmountCol) {
                    // Check for both possible formats
                    if (!array_key_exists('Medicare Amount', $row) && !array_key_exists('Medicare Amount ($)', $row)) {
                        $errors["data.{$index}.Medicare Amount"] = ["The data.{$index}.Medicare Amount field is required."];
                    }
                }
                
                if ($providerAmountCol && !array_key_exists($providerAmountCol, $row)) {
                    $errors["data.{$index}.{$providerAmountCol}"] = ["The data.{$index}.{$providerAmountCol} field is required."];
                } elseif (!$providerAmountCol) {
                    // Check for both possible formats
                    if (!array_key_exists('Provider Amount', $row) && !array_key_exists('Provider Amount ($)', $row)) {
                        $errors["data.{$index}.Provider Amount"] = ["The data.{$index}.Provider Amount field is required."];
                    }
                }
                
                if (!empty($errors)) {
                    return response()->json([
                        'message' => 'Validation failed',
                        'errors' => $errors
                    ], 422);
                }
            }
        }
         
        $processedData = [];
        $seenRecords = [];  
        foreach ($data as $index => $item) { 
            $medicareAmountCol = null;
            $providerAmountCol = null;
             
            foreach (['Medicare Amount', 'Medicare Amount ($)', 'Medicare_Amount', 'medicare_amount', 'Invoice Paid Amount'] as $col) {
                if (isset($item[$col])) {
                    $medicareAmountCol = $col;
                    break;
                }
            }
             
            foreach (['Provider Amount', 'Provider Amount ($)', 'Provider_Amount', 'provider_amount', 'Provider Balance', 'Provider Balance Owed', 'Provider Amount Owed'] as $col) {
                if (isset($item[$col])) {
                    $providerAmountCol = $col;
                    break;
                }
            }
             
            if (!$providerAmountCol) { 
                $providerAmountCol = $medicareAmountCol ?: null;
            }
            
            if (!$medicareAmountCol || !$providerAmountCol) {
                $missingCols = [];
                if (!$medicareAmountCol) $missingCols[] = 'Medicare Amount';
                if (!$providerAmountCol) $missingCols[] = 'Provider Amount';
                
                return response()->json([
                    'message' => 'Missing required data',
                    'errors' => [
                        "data.{$index}" => ['Missing required columns: ' + implode(', ', $missingCols)]
                    ]
                ], 422);
            }
            
            // Clean and convert numeric values
            $medicareAmount = $this->cleanNumericValue($item[$medicareAmountCol] ?? '');
            // If both amounts use the same column, use the same value; otherwise use the provider-specific column
            $providerAmount = ($providerAmountCol === $medicareAmountCol) ? $medicareAmount : $this->cleanNumericValue($item[$providerAmountCol] ?? '');
            
            \Log::info('Processing row ' . ($index + 1), [
                'original_medicare' => $item[$medicareAmountCol] ?? 'NULL',
                'medicare_column' => $medicareAmountCol,
                'cleaned_medicare' => $medicareAmount,
                'original_provider' => $item[$providerAmountCol] ?? 'NULL',
                'provider_column' => $providerAmountCol,
                'cleaned_provider' => $providerAmount
            ]);
            
            if ($medicareAmount === false || $providerAmount === false) {
                $errors = [];
                if ($medicareAmount === false) {
                    $errors["data.{$index}"] = ['Medicare Amount is not a valid number'];
                }
                if ($providerAmount === false && $providerAmountCol !== $medicareAmountCol) {
                    $errors["data.{$index}"] = ['Provider Amount is not a valid number'];
                }
                
                return response()->json([
                    'message' => 'Invalid number format',
                    'errors' => $errors
                ], 422);
            }
            
            // Create a unique key for duplicate detection
            // Using patient name, invoice number, service date, and amounts as the uniqueness criteria
            $uniqueKey = md5(
                strtolower(trim($item['Patient Name'] ?? '')) . '|' .
                trim($item['Invoice Number'] ?? '') . '|' .
                trim($item['Service Date'] ?? '') . '|' .
                $medicareAmount . '|' .
                $providerAmount
            );
            
            // Check for duplicates
            if (isset($seenRecords[$uniqueKey])) {
                $existingIndex = $seenRecords[$uniqueKey];
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        "data.{$index}" => ["Duplicate record found. This record matches row {$existingIndex} (same patient, invoice, service date, and amounts)"]
                    ]
                ], 422);
            }
            
            $seenRecords[$uniqueKey] = $index + 1; // Store 1-based index for user-friendly error messages
            
            // Map the values to standard column names
            $processedRow = $item;
            $processedRow['Medicare Amount'] = $medicareAmount;
            $processedRow['Provider Amount'] = $providerAmount;
            
            // Handle optional fields
            if (isset($item['Submission Date'])) {
                $processedRow['Submission Date'] = $item['Submission Date'];
            } elseif (isset($item['Submission_Date'])) {
                $processedRow['Submission Date'] = $item['Submission_Date'];
            } elseif (isset($item['submission_date'])) {
                $processedRow['Submission Date'] = $item['submission_date'];
            }
            
            if (isset($item['Clinician'])) {
                $processedRow['Clinician'] = $item['Clinician'];
            } elseif (isset($item['clinician'])) {
                $processedRow['Clinician'] = $item['clinician'];
            }
            
            if (isset($item['Notes'])) {
                $processedRow['Notes'] = $item['Notes'];
            } elseif (isset($item['notes'])) {
                $processedRow['Notes'] = $item['notes'];
            }
            
            $processedData[] = $processedRow;
        }

        $imported = [];
        $errors = [];

        foreach ($processedData as $index => $item) {
            try {
                // Normalize status values
                $status = strtolower(trim($item['Status']));
                $normalizedStatus = $status;
                
                // Handle common status variations
                $statusMap = [
                    'paid' => 'paid',
                    'payment received' => 'paid',
                    'received' => 'paid',
                    'complete' => 'paid',
                    'processed' => 'processed',
                    'submitted' => 'submitted',
                    'pending' => 'pending',
                    'awaiting' => 'pending',
                    'in progress' => 'submitted',
                    'denied' => 'denied',
                    'rejected' => 'denied',
                    'cancelled' => 'denied'
                ];
                
                foreach ($statusMap as $key => $value) {
                    if (strpos($status, $key) !== false) {
                        $normalizedStatus = $value;
                        break;
                    }
                }
                
                // Convert date formats to standard format
                $serviceDate = $this->convertDateFormat($item['Service Date'] ?? '');
                $submissionDate = $this->convertDateFormat($item['Submission Date'] ?? '');
                
                // Check for duplicates in the database (cross-file validation)
                $existingRecord = BillerTracking::where('patient_name', trim($item['Patient Name'] ?? ''))
                    ->where('invoice_number', trim($item['Invoice Number'] ?? ''))
                    ->where('service_date', $serviceDate)
                    ->where('medicare_amount', $medicareAmount)
                    ->where('provider_amount', $providerAmount)
                    ->first();
                
                if ($existingRecord) {
                    return response()->json([
                        'message' => 'Duplicate entry detected',
                        'errors' => [
                            "data.{$index}" => ["This record already exists in the system. Please check your data and try again."]
                        ]
                    ], 422);
                }
                
                // Validate converted dates
                if (empty($serviceDate) && !empty($item['Service Date'])) {
                    throw new \Exception("Invalid Service Date format: {$item['Service Date']}. Expected format like MM-DD-YYYY.");
                }
                // Only validate submission date if it was provided and not empty
                if (!empty($item['Submission Date']) && !empty($submissionDate) && $submissionDate !== 'null' && $submissionDate !== null && !$this->isValidDate($submissionDate)) {
                    throw new \Exception("Invalid Submission Date format: {$item['Submission Date']}.");
                }
                
                // Set submission date to null if it's empty, null, or invalid
                if (empty($submissionDate) || $submissionDate === 'null' || $submissionDate === '' || $submissionDate === '""') {
                    $submissionDate = null;
                }
                
                // Ensure submission date is properly formatted if not null
                if ($submissionDate !== null && !$this->isValidDate($submissionDate)) {
                    $submissionDate = null; // Fallback to null if conversion failed
                }
                
                $billerData = [
                    'patient_name' => $item['Patient Name'],
                    'invoice_number' => $item['Invoice Number'],
                    'service_date' => $serviceDate,
                    'submission_date' => $submissionDate, // Will be null if empty/invalid
                    'medicare_amount' => $item['Medicare Amount ($)'] ?? $item['Medicare Amount'],  
                    'provider_amount' => $item['Provider Amount ($)'] ?? $item['Provider Amount'],  
                    'status' => $normalizedStatus,
                    'clinician' => $item['Clinician'] ?? null,
                    'notes' => $item['Notes'] ?? null
                ];

                $biller = BillerTracking::create($billerData);
                \Log::info('Biller tracking record created', [
                    'id' => $biller->id,
                    'patient_name' => $biller->patient_name,
                    'submission_date' => $biller->submission_date,
                    'original_submission_date' => $item['Submission Date'] ?? 'NOT_PROVIDED'
                ]);
                $imported[] = $biller;
            } catch (\Exception $e) {
                $errors[] = [
                    'row' => $index + 1,
                    'error' => $e->getMessage(),
                    'original_status' => $item['Status'] ?? 'N/A',
                    'processed_status' => $normalizedStatus ?? 'N/A'
                ];
            }
        }
    
        return response()->json([
            'imported' => $imported,
            'errors' => $errors,
            'message' => sprintf('Imported %d records, %d errors', count($imported), count($errors))
        ]);
    }
    
    private function cleanNumericValue($value) {
            if ($value === '' || $value === null) {
                return false;
            }
            
            // Remove currency symbols, commas, and whitespace
            $cleaned = preg_replace('/[,$\s]/', '', trim($value));
            
            // Handle negative values
            $isNegative = strpos($cleaned, '-') === 0;
            $cleaned = ltrim($cleaned, '-');
            
            // Validate it's a number
            if (!is_numeric($cleaned)) {
                return false;
            }
            
            $number = floatval($cleaned);
            return $isNegative ? -$number : $number;
        }
        
        private function convertDateFormat($dateString) {
        if (empty($dateString)) {
            return null;
        }
        
        // Try various date formats
        $formats = [
            'm-d-Y',      // 02-03-2026
            'd-m-Y',      // 03-02-2026
            'Y-m-d',      // 2026-02-03
            'm/d/Y',      // 02/03/2026
            'd/m/Y',      // 03/02/2026
            'Y/m/d',      // 2026/02/03
            'm.d.Y',      // 02.03.2026
            'd.m.Y',      // 03.02.2026
            'Y.m.d',      // 2026.02.03
            'F j, Y',     // February 3, 2026
            'j F Y',      // 3 February 2026
        ];
        
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }
        
        // If no format matches, try strtotime as fallback
        $timestamp = strtotime($dateString);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
        
        // If all else fails, return original string (validation will catch it)
        return $dateString;
    }
    
    private function isValidDate($dateString) {
        if (empty($dateString)) {
            return false;
        }
        
        $date = \DateTime::createFromFormat('Y-m-d', $dateString);
        return $date && $date->format('Y-m-d') === $dateString;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $biller = BillerTracking::findOrFail($id);
        return response()->json($biller);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $biller = BillerTracking::findOrFail($id);
        
        $validated = $request->validate([
            'patient_name' => 'sometimes|required|string|max:255',
            'invoice_number' => 'sometimes|required|string|max:255',
            'service_date' => 'sometimes|required|date',
            'submission_date' => 'nullable|date',
            'medicare_amount' => 'sometimes|required|numeric|min:0',
            'provider_amount' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|string|in:pending,submitted,processed,paid,denied',
            'clinician' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);
        
        // Additional validation: submission date should not be before service date
        $serviceDate = $validated['service_date'] ?? $biller->service_date;
        $submissionDate = $validated['submission_date'] ?? $biller->submission_date;
        
        if (!empty($submissionDate) && !empty($serviceDate)) {
            $serviceDateTime = new \DateTime($serviceDate);
            $submissionDateTime = new \DateTime($submissionDate);
            
            if ($submissionDateTime < $serviceDateTime) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'submission_date' => ['Submission date cannot be before service date']
                    ]
                ], 422);
            }
        }
        
        $biller->update($validated);
        return response()->json($biller);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $biller = BillerTracking::findOrFail($id);
        $biller->delete();
        return response()->json(null, 204);
    }
}
