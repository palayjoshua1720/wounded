<?php
// app/Http/Controllers/InvoiceController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    // Update the index method to join with woundmed_clinics
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::with(['clinic' => function ($query) {
            $query->select('clinic_id', 'clinic_code', 'clinic_name', 'email', 'contact_person', 'phone', 'address');
        }]);

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('order_id', 'like', "%{$search}%")
                    ->orWhereHas('clinic', function ($q) use ($search) {
                        $q->where('clinic_name', 'like', "%{$search}%")
                            ->orWhere('clinic_code', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Clinic filter
        if ($request->has('clinic_id') && $request->clinic_id !== 'all') {
            $query->where('clinic_id', $request->clinic_id);
        }

        // Date filters
        if ($request->has('date_from') && $request->date_from) {
            $query->where('invoice_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('invoice_date', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($invoices);
    }

    public function getClinics(): JsonResponse
    { 
        $clinics = Clinic::active()
            ->select('clinic_id', 'clinic_code', 'clinic_name', 'email', 'contact_person', 'phone', 'address')
            ->orderBy('clinic_name')
            ->get();

        return response()->json($clinics);
    }

    public function getStats(): JsonResponse
    {
        $stats = [
            'total_invoices'  => Invoice::count(),
            'pending_review'  => Invoice::where('needs_review', true)->count(),
            'pending_payment' => Invoice::where('status', 'pending')->count(),
            'sync_required'   => Invoice::where('sync_status', 'out_of_sync')->count(),
            'total_amount'    => Invoice::sum('amount'),
            'paid_amount'     => Invoice::where('status', 'paid')->sum('amount'),
            'pending_amount'  => Invoice::where('status', 'pending')->sum('amount'),
            'overdue_amount'  => Invoice::where('status', 'overdue')->sum('amount'),
        ];

        return response()->json($stats);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json($invoice->load('clinic'));
    }

    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'invoice_number' => 'sometimes|required|string|unique:invoices,invoice_number,' . $invoice->id,
            'clinic_id'      => 'sometimes|required|exists:woundmed_clinics,clinic_id',
            'amount'         => 'sometimes|required|numeric|min:0',
            'invoice_date'   => 'sometimes|required|date',
            'due_date'       => 'sometimes|required|date',
            'status'         => 'sometimes|required|in:pending_review,pending,paid,overdue,cancelled',
            'order_id'       => 'nullable|string',
            'serials'        => 'nullable|array',
            'serials.*'      => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = $request->all();

        // Handle paid date automatically
        if ($request->has('status')) {
            if ($request->status === 'paid' && $invoice->status !== 'paid') {
                $updateData['paid_date'] = now();
            } elseif ($request->status !== 'paid') {
                $updateData['paid_date'] = null;
            }
        }

        $invoice->update($updateData);

        return response()->json(['message' => 'Invoice updated successfully', 'invoice' => $invoice->load('clinic')]);
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        // Delete associated PDF file if exists
        if ($invoice->pdf_path && Storage::exists($invoice->pdf_path)) {
            Storage::delete($invoice->pdf_path);
        }

        $invoice->delete();

        return response()->json(['message' => 'Invoice deleted successfully']);
    }

    public function updateStatus(Request $request, Invoice $invoice): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status'            => 'required|in:pending,paid,overdue,cancelled',
            'paid_amount'       => 'nullable|numeric|min:0',
            'payment_method'    => 'nullable|string',
            'payment_reference' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = [
            'status'       => $request->status,
            'needs_review' => false,
        ];

        if ($request->status === 'paid') {
            $updateData['paid_date']         = now();
            $updateData['paid_amount']       = $request->paid_amount ?? $invoice->amount;
            $updateData['partial_payment']   = $request->paid_amount && $request->paid_amount < $invoice->amount;
            $updateData['payment_method']    = $request->payment_method;
            $updateData['payment_reference'] = $request->payment_reference;
        } else {
            $updateData['paid_date']       = null;
            $updateData['paid_amount']     = null;
            $updateData['partial_payment'] = false;
        }

        $invoice->update($updateData);

        return response()->json(['message' => 'Invoice status updated successfully', 'invoice' => $invoice->load('clinic')]);
    }

    public function uploadPdf(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pdf_file' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $file = $request->file('pdf_file');
            $path = $file->store('invoices/pdfs', 'public');

            // Here you would integrate with your OCR service
            // For now, we'll return a mock response
            $extractedData = [
                'invoice_number' => 'INV-' . now()->format('Ymd-His'),
                'amount'         => rand(1000, 5000),
                'invoice_date'   => now()->format('Y-m-d'),
                'due_date'       => now()->addDays(30)->format('Y-m-d'),
                'serials'        => ['GS' . rand(1000, 9999), 'GS' . rand(1000, 9999)],
            ];

            return response()->json([
                'message'        => 'PDF uploaded successfully',
                'path'           => $path,
                'extracted_data' => $extractedData,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to process PDF: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'invoice_number' => 'required|string|unique:invoices',
            'clinic_id'      => 'required|exists:woundmed_clinics,clinic_id',
            'amount'         => 'required|numeric|min:0',
            'invoice_date'   => 'required|date',
            'due_date'       => 'required|date',
            'status'         => 'required|in:pending_review,pending,paid,overdue,cancelled',
            'order_id'       => 'nullable|string',
            'serials'        => 'nullable|array',
            'serials.*'      => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verify clinic exists and is active
        $clinic = Clinic::active()->find($request->clinic_id);
        if (! $clinic) {
            return response()->json(['errors' => ['clinic_id' => ['Selected clinic is not active or does not exist']]], 422);
        }

        $invoice = Invoice::create([
            'invoice_number' => $request->invoice_number,
            'clinic_id'      => $request->clinic_id,
            'amount'         => $request->amount,
            'invoice_date'   => $request->invoice_date,
            'due_date'       => $request->due_date,
            'status'         => $request->status,
            'order_id'       => $request->order_id,
            'serials'        => $request->serials ?? [],
            'needs_review'   => $request->status === 'pending_review',
            'paid_date'      => $request->status === 'paid' ? now() : null,
        ]);

        return response()->json(['message' => 'Invoice created successfully', 'invoice' => $invoice->load('clinic')], 201);
    }
}
