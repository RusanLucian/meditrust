<?php
require_once 'bootstrap.php';

echo "Test 1: PHP funcționează<br>";

echo "Test 2: Config loaded<br>";

// Test conexiune
if ($conn->connect_error) {
    echo "Eroare DB: " . $conn->connect_error;
} else {
    echo "Test 3: Conexiune OK<br>";
}

// Test query
$result = $conn->query("SELECT * FROM users LIMIT 1");
if ($result) {
    echo "Test 4: Query funcționează<br>";
} else {
    echo "Eroare query: " . $conn->error;
}
?>