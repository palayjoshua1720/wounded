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
        $validated = $request->validate([
            'data' => 'required|array',
            'data.*.Patient Name' => 'required|string|max:255',
            'data.*.Invoice Number' => 'required|string|max:255',
            'data.*.Service Date' => 'required|string',
            'data.*.Submission Date' => 'nullable|string',
            'data.*.Medicare Amount ($)' => 'required|numeric|min:0',
            'data.*.Provider Amount ($)' => 'required|numeric|min:0',
            'data.*.Status' => 'required|string',
            'data.*.Clinician' => 'nullable|string|max:255',
            'data.*.Notes' => 'nullable|string'
        ]);

        $imported = [];
        $errors = [];

        foreach ($request->data as $index => $item) {
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
                
                // Validate converted dates
                if (empty($serviceDate) && !empty($item['Service Date'])) {
                    throw new \Exception("Invalid Service Date format: {$item['Service Date']}. Expected format like MM-DD-YYYY.");
                }
                // Only validate submission date if it was provided and not empty
                if (!empty($item['Submission Date']) && !empty($submissionDate) && $submissionDate !== 'null' && $submissionDate !== null && !$this->isValidDate($submissionDate)) {
                    throw new \Exception("Invalid Submission Date format: {$item['Submission Date']}.");
                }
                
                // Set submission date to null if it's empty after conversion
                if (empty($submissionDate) || $submissionDate === 'null') {
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
                    'submission_date' => (empty($submissionDate) || $submissionDate === 'null') ? null : $submissionDate,
                    'medicare_amount' => $item['Medicare Amount ($)'],
                    'provider_amount' => $item['Provider Amount ($)'],
                    'status' => $normalizedStatus,
                    'clinician' => $item['Clinician'] ?? null,
                    'notes' => $item['Notes'] ?? null
                ];

                $biller = BillerTracking::create($billerData);
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
