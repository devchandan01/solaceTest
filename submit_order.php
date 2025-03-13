<?php
include './dbConnection/db.php';
$db = new Database();
$conn = $db->getConnection();

$customer_name = trim($_POST['customer_name']);
$address = trim($_POST['address']);
$subtotal = floatval($_POST['subtotal']);
$tax = floatval($_POST['tax']);
$total = floatval($_POST['total']);


$stmt = $conn->prepare("INSERT INTO orders (customer_name, address, subtotal, tax, total) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$customer_name, $address, $subtotal, $tax, $total]);
$order_id = $conn->lastInsertId();

// Inserting order details
foreach ($_POST['product_id'] as $index => $product_id) {
    $cost = floatval($_POST['total_cost'][$index] / $_POST['qty'][$index]);
    $qty = intval($_POST['qty'][$index]);
    $total_cost = floatval($_POST['total_cost'][$index]);

    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, cost, qty, total_cost) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$order_id, $product_id, $cost, $qty, $total_cost]);
}

// Retrieving the details of the ordered items for the recently placed order.
$stmt = $conn->prepare("
    SELECT 
        o.id AS order_id, 
        o.customer_name, 
        o.address, 
        o.subtotal, 
        o.tax, 
        o.total, 
        p.name AS product_name, 
        oi.qty, 
        oi.cost, 
        oi.total_cost
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$orderDetails = $stmt->fetchAll();

if ($orderDetails) {
    echo "<div class='col-12 col-lg-6  card p-3'>";
    echo "<div class='row'>";
    echo "<p  class='col-12 col-lg-6 pb-1'><strong>Order ID:</strong> " . htmlspecialchars($orderDetails[0]['order_id']) . "</p>";
    echo "<p  class='col-12 col-lg-6 pb-1'><strong>Customer:</strong> " . htmlspecialchars($orderDetails[0]['customer_name']) . "</p>";
    echo "<p  class='col-12 col-lg-6 p pb-1'><strong>Address:</strong> " . htmlspecialchars($orderDetails[0]['address']) . "</p>";
    echo "<p  class='col-12 col-lg-6 p pb-1'><strong>Subtotal:</strong> ₹" . number_format($orderDetails[0]['subtotal'], 2) . "</p>";
    echo "<p  class='col-12 col-lg-6 p pb-1'><strong>Tax (18%):</strong> ₹" . number_format($orderDetails[0]['tax'], 2) . "</p>";
    echo "<p  class='col-12 col-lg-6 p pb-1'><strong>Total:</strong> ₹" . number_format($orderDetails[0]['total'], 2) . "</p>";

    echo "<h4 class='col-12 p-2'>Ordered Products</h4>";
    echo "<table border='1' cellpadding='5' cellspacing='0' class='px-3'>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Cost</th>
                <th>Total Cost</th>
            </tr>";
    foreach ($orderDetails as $row) {
        echo "<tr>
                <td>" . htmlspecialchars($row['product_name']) . "</td>
                <td>" . htmlspecialchars($row['qty']) . "</td>
                <td>₹" . number_format($row['cost'], 2) . "</td>
                <td>₹" . number_format($row['total_cost'], 2) . "</td>
              </tr>";
    }
    echo "</table>";
    echo "</div>";
    echo "</div>";
} else {
    echo "<p>Order details not found.</p>";
}