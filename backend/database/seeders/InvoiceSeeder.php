<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, let's check if we have any clinics
        $clinics = DB::table('woundmed_clinics')->pluck('clinic_id')->toArray();
        
        // if (empty($clinics)) {
        //     // If no clinics exist, let's create a sample clinic first
        //     $clinicId = DB::table('woundmed_clinics')->insertGetId([
        //         'clinic_name' => 'Sample Clinic',
        //         'clinic_code' => 'CL-' . now()->format('Ymd') . '-0001',
        //         'email' => 'sample@clinic.com',
        //         'contact_person' => 'John Doe',
        //         'phone' => '555-123-4567',
        //         'address' => '123 Medical Drive, Health City, HC 12345',
        //         'clinic_status' => 0,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]);
        //     $clinics = [$clinicId];
        // }
        
        // Sample invoice data
        $invoices = [
            [
                'invoice_number' => 'INV-2025-001',
                'clinic_id' => $clinics[0],
                'amount' => 1250.00,
                'status' => 'paid',
                'invoice_date' => '2025-10-15',
                'due_date' => '2025-11-15',
                'paid_date' => '2025-10-20',
                'order_id' => 'ORD-2025-001',
                'serials' => json_encode(['GS1234', 'GS5678']),
                'notes' => 'Payment received on time',
                'needs_review' => false,
                'sync_status' => 'synced',
                'partial_payment' => false,
                'paid_amount' => 1250.00,
                'payment_method' => 'Credit Card',
                'payment_reference' => 'CC-REF-001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'invoice_number' => 'INV-2025-002',
                'clinic_id' => $clinics[0],
                'amount' => 2100.50,
                'status' => 'pending',
                'invoice_date' => '2025-11-01',
                'due_date' => '2025-12-01',
                'serials' => json_encode(['GS9012', 'GS3456', 'GS7890']),
                'notes' => 'Awaiting payment',
                'needs_review' => false,
                'sync_status' => 'synced',
                'partial_payment' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'invoice_number' => 'INV-2025-003',
                'clinic_id' => $clinics[0],
                'amount' => 875.75,
                'status' => 'overdue',
                'invoice_date' => '2025-10-01',
                'due_date' => '2025-11-01',
                'serials' => json_encode(['GS1111', 'GS2222']),
                'notes' => 'Payment overdue by 12 days',
                'needs_review' => true,
                'sync_status' => 'out_of_sync',
                'partial_payment' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'invoice_number' => 'INV-2025-004',
                'clinic_id' => $clinics[0],
                'amount' => 3200.00,
                'status' => 'pending_review',
                'invoice_date' => '2025-11-10',
                'due_date' => '2025-12-10',
                'serials' => json_encode(['GS3333', 'GS4444', 'GS5555', 'GS6666']),
                'notes' => 'New invoice requiring review',
                'needs_review' => true,
                'sync_status' => 'out_of_sync',
                'partial_payment' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        // Insert the sample invoices
        foreach ($invoices as $invoice) {
            // Check if invoice already exists to avoid duplicates
            $exists = DB::table('woundmed_invoices')
                ->where('invoice_number', $invoice['invoice_number'])
                ->exists();
                
            if (!$exists) {
                DB::table('woundmed_invoices')->insert($invoice);
            }
        }
    }
}