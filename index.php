<?php
include './dbConnection/db.php';
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->prepare("SELECT * FROM products");
$stmt->execute();
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Placement</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

</head>

<body>
    <div class="container">
        <h2 class='text-center p-3 text-dark mb-3' style="box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;">
            Place Order</h2>
        <form id="orderForm">
            <div class="row">
                <div class="form-group col-lg-6 col-md-6 col-12">
                    <label>Customer Name:</label>
                    <input type="text" name="customer_name" class="form-control" required>
                </div>
                <div class="form-group col-lg-6 col-md-6 col-12">
                    <label>Address:</label>
                    <textarea name="address" class="form-control" required></textarea>

                </div>
            </div>

            <table border="1" class="table" id="orderTable">
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Cost</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
                <tr class="order-row">
                    <td>
                        <select name="product_id[]" class="product form-control" required>
                            <option value="">Select Product</option>
                            <?php foreach ($products as $row) { ?>
                            <option value="<?= htmlspecialchars($row['id']) ?>"
                                data-cost="<?= htmlspecialchars($row['cost']) ?>">
                                <?= htmlspecialchars($row['name']) ?> - ₹<?= htmlspecialchars($row['cost']) ?>
                            </option>
                            <?php } ?>
                        </select>
                    </td>
                    <td><input type="number" class="form-control" name="qty[]" class="qty" min="1" value="1"></td>
                    <td><input type="text" class="cost form-control" readonly></td>
                    <td><input type="text" name="total_cost[]" class="total_cost form-control" readonly></td>
                    <td><button type="button" class="removeRow btn btn-danger">Remove</button></td>
                </tr>
            </table>

            <button type="button" id="addRow" class="btn btn-success mb-5">Add Product</button>

            <div class="row">
                <div class="form-group col-lg-4 col-md-6 col-12">
                    <label>Subtotal:</label>
                    <input type="text" class="form-control" id="subtotal" name="subtotal" readonly>
                </div>
                <div class="form-group col-lg-4 col-md-6 col-12">
                    <label>Tax (18%):</label>
                    <input type="text" class="form-control" id="tax" name="tax" readonly>
                </div>
                <div class="form-group col-lg-4 col-md-6 col-12">
                    <label>Total:</label>
                    <input type="text" class="form-control" id="total" name="total" readonly>
                </div>
            </div>
            <button type="submit" class="btn btn-info">Place Order</button>
        </form>

        <h2 class="py-3">Order Details</h2>
        <div id="orderDetails" class="row"></div>

    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.min.js"
        integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous">
    </script>
    <script>
    $(document).ready(function() {
        function calculateTotal() {
            let subtotal = 0;
            $(".order-row").each(function() {
                let total = parseFloat($(this).find(".total_cost").val()) || 0;
                subtotal += total;
            });

            let tax = (subtotal * 18) / 100;
            let total = subtotal + tax;

            $("#subtotal").val(subtotal.toFixed(2));
            $("#tax").val(tax.toFixed(2));
            $("#total").val(total.toFixed(2));
        }

        $("#orderTable").on("change", ".product, .qty", function() {
            let row = $(this).closest(".order-row");
            let cost = parseFloat(row.find(".product option:selected").data("cost")) || 0;
            let qty = parseInt(row.find(".qty").val()) || 1;
            let total = cost * qty;

            row.find(".cost").val(cost.toFixed(2));
            row.find(".total_cost").val(total.toFixed(2));

            calculateTotal();
        });

        $("#addRow").click(function() {
            let newRow = $(".order-row:first").clone();
            newRow.find("input, select").val("");
            $("#orderTable").append(newRow);
        });

        $("#orderTable").on("click", ".removeRow", function() {
            if ($(".order-row").length > 1) {
                $(this).closest(".order-row").remove();
                calculateTotal();
            }
        });

        $("#orderForm").submit(function(e) {
            e.preventDefault();
            $.post("submit_order.php", $(this).serialize(), function(data) {
                $("#orderDetails").append(data);
                $("#orderForm")[0].reset();
                calculateTotal();
            });
        });
    });
    </script>
</body>

</html>