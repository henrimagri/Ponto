<?php
// Simple test to verify the Laravel Employee Management System

echo "=== Laravel Employee Management System Test ===\n\n";

// Test 1: Check if we can connect to the application
echo "1. Testing basic connectivity...\n";
$response = file_get_contents('http://127.0.0.1:8000/login');
if ($response !== false && strpos($response, 'Login') !== false) {
    echo "✅ Login page loads successfully\n";
} else {
    echo "❌ Failed to load login page\n";
}

// Test 2: Check database connection by testing a simple query
echo "\n2. Testing database connection...\n";
try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    $userCount = \App\Models\User::count();
    echo "✅ Database connection successful. Found {$userCount} users.\n";
    
    // List test users
    $users = \App\Models\User::select('email', 'role')->get();
    echo "Test users available:\n";
    foreach ($users as $user) {
        echo "  - {$user->email} ({$user->role})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "\nYou can now test the system manually:\n";
echo "1. Open http://127.0.0.1:8000/login\n";
echo "2. Login with:\n";
echo "   - Admin: admin@admin.com / 123456\n";
echo "   - Manager: gestor@teste.com / 123456\n";
echo "   - Employee: funcionario@teste.com / 123456\n";
echo "3. Test CRUD operations:\n";
echo "   - Create new users (Admin/Manager only)\n";
echo "   - View user details\n";
echo "   - Edit user information\n";
echo "   - Delete users (Admin only)\n";
echo "4. Test CEP lookup functionality\n";
echo "5. Test role-based access control\n";
