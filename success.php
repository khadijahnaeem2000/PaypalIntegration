<?php
// success.php

// === DB CONNECTION ===
$host = "localhost"; 
$user = "root"; 
$pass = ""; 
$dbname = "paypal"; // <-- change to your DB name
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}

// === Get payment data ===
$orderID = $_GET['orderID'] ?? null;
$device_token = $_GET['device_token'] ?? null;
$amount = $_GET['amount'] ?? 0;
$method = $_GET['method'] ?? 'paypal';

// Optional: fetch payer info from PayPal API (or passed via GET)
$payer_name = $_GET['payer_name'] ?? 'Unknown';
$payer_email = $_GET['payer_email'] ?? 'Unknown';

// === Save to DB ===
$stmt = $conn->prepare("INSERT INTO payments (payer_name, payer_email, device_token, amount, payment_method, order_id) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssiss", $payer_name, $payer_email, $device_token, $amount, $method, $orderID);

if ($stmt->execute()) {
    echo "<h2 style='font-family:sans-serif;color:green;text-align:center;margin-top:50px;'>✅ Payment saved successfully!</h2>";
} else {
    echo "<h2 style='color:red;text-align:center;margin-top:50px;'>❌ Error saving payment: " . $stmt->error . "</h2>";
}

$stmt->close();
$conn->close();
?>
