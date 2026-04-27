<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Clinic;
use Illuminate\Support\Facades\DB;

echo "=== Testing NEW Clinic Creation ===\n\n";

// Delete the existing clinic that's causing the duplicate issue
DB::table('woundmed_clinics')->where('clinic_code', 'CL-20260327-0002')->delete();

echo "Creating new clinic...\n\n";

$clinic = Clinic::create([
    'clinic_name' => 'Test New Clinic ' . time(),
    'email' => 'test-new-' . time() . '@test.com',
    'clinic_status' => 0,
]);

echo "Clinic created with ID: " . $clinic->clinic_id . "\n\n";

echo "1. Raw attribute (getRawOriginal - from DB):\n";
$rawName = $clinic->getRawOriginal('clinic_name');
echo "   clinic_name (raw): " . (is_string($rawName) ? substr($rawName, 0, 100) : 'NULL') . "\n";
echo "   email (raw): " . substr($clinic->getRawOriginal('email'), 0, 100) . "\n";

echo "\n2. Accessed attribute (via ->clinic_name):\n";
echo "   clinic_name: " . $clinic->clinic_name . "\n";
echo "   email: " . $clinic->email . "\n";

echo "\n3. Check if raw value is encrypted format:\n";
$decoded = base64_decode($rawName, true);
$json = $decoded ? json_decode($decoded, true) : null;
$isEncryptedFormat = $json && isset($json['iv']) && isset($json['value']) && isset($json['mac']);
echo "   has encrypted format: " . ($isEncryptedFormat ? 'YES' : 'NO') . "\n";

echo "\n4. attributesToArray() result:\n";
$array = $clinic->attributesToArray();
echo "   clinic_name: " . ($array['clinic_name'] ?? 'NULL') . "\n";
echo "   email: " . ($array['email'] ?? 'NULL') . "\n";

echo "\n5. Check what's actually in database (fresh query):\n";
$freshClinic = Clinic::find($clinic->clinic_id);
echo "   clinic_name (fresh): " . $freshClinic->clinic_name . "\n";
echo "   email (fresh): " . $freshClinic->email . "\n";

// Cleanup
$clinic->delete();
echo "\nTest clinic deleted.\n";
