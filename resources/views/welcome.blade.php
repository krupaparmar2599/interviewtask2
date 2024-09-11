<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Form</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/parsleyjs@2.9.2/src/parsley.css">
    <style>
        .parsley-errors-list {
            color: red;
            list-style-type: none;
            padding: 0;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <form id="invoice-form" action="{{ route('invoice.save') }}" method="POST" data-parsley-validate>
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label for="customer_name">Customer Name welcome </label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" required data-parsley-required-message="Please enter customer name">
                    </div>

                    <div class="form-group">
                        <label for="customer_email">Customer Email</label>
                        <input type="email" class="form-control" id="customer_email" name="customer_email" required data-parsley-required-message="Please enter email address" data-parsley-type="email" data-parsley-type-message="Please enter a valid email address">
                    </div>

                    <h5>Products</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Discount (%)</th>
                            </tr>
                        </thead>
                        <tbody id="product-table-body">
                            <tr>
                                <td>
                                    <input type="text" class="form-control" name="products[0][name]" required data-parsley-required-message="Please enter product name">
                                </td>
                                <td>
                                    <input type="number" class="form-control" name="products[0][price]" step="0.01" required data-parsley-required-message="Please enter price" data-parsley-min="0.01" data-parsley-min-message="Price must be greater than zero">
                                </td>
                                <td>
                                    <input type="number" class="form-control" name="products[0][discount]" step="0.01">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-secondary" id="add-product-btn">Add Product</button>

                    <div class="mt-4">
                        <div class="form-group">
                            <label>Total Items</label>
                            <input type="number" class="form-control" id="total-items" readonly>
                        </div>
                        <div class="form-group">
                            <label>Total Amount</label>
                            <input type="number" class="form-control" id="total-amount" readonly>
                        </div>
                        <div class="form-group">
                            <label>Total Discount Amount</label>
                            <input type="text" class="form-control" id="total-discount-amount" readonly>
                        </div>
                        <div class="form-group">
                            <label>Total Bill</label>
                            <input type="text" class="form-control" id="total-bill" readonly>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/parsleyjs@2.9.2/dist/parsley.min.js"></script>


    <script>
        $(document).ready(function() {
            let productCount = 1;
            $('#add-product-btn').on('click', function() {
                let newRow = `
                <tr>
                    <td>
                        <input type="text" class="form-control" name="products[${productCount}][name]" required data-parsley-required-message="Please enter product name">
                    </td>
                    <td>
                        <input type="number" class="form-control" name="products[${productCount}][price]" step="0.01" required data-parsley-required-message="Please enter valid price" data-parsley-min="0.01" data-parsley-min-message="Price must be greater than zero.">
                    </td>
                    <td>
                        <input type="number" class="form-control" name="products[${productCount}][discount]" step="0.01"">
                    </td>
                </tr>`;
                $('#product-table-body').append(newRow);
                productCount++;
            });
            $('#invoice-form').parsley();
        });
        document.addEventListener('DOMContentLoaded', function() {
            // Function to calculate totals
            function calculateTotals() {
                const rows = document.querySelectorAll('#product-table-body tr');
                let totalItems = 0;
                let totalAmount = 0;
                let totalDiscountAmount = 0;
                let totalBill = 0;

                rows.forEach(row => {
                    const priceInput = row.querySelector('input[name$="[price]"]');
                    const discountInput = row.querySelector('input[name$="[discount]"]');

                    const price = parseFloat(priceInput.value) || 0;
                    const discount = parseFloat(discountInput.value) || 0;

                    if (!isNaN(price) && !isNaN(discount)) {
                        totalItems += 1;
                        totalAmount += price;
                        totalDiscountAmount += (price * (discount / 100));
                        totalBill += (price - (price * (discount / 100)));
                    }
                });

                // Update the fields
                document.getElementById('total-items').value = totalItems;
                document.getElementById('total-amount').value = totalAmount.toFixed(2);
                document.getElementById('total-discount-amount').value = totalDiscountAmount.toFixed(2);
                document.getElementById('total-bill').value = totalBill.toFixed(2);
            }

            // Event listeners for input changes
            document.querySelector('#product-table-body').addEventListener('input', function(event) {
                if (event.target.matches('input[name$="[price]"]') || event.target.matches('input[name$="[discount]"]')) {
                    calculateTotals();
                }
            });

            // Initial calculation
            calculateTotals();
        });

        // document.addEventListener('DOMContentLoaded', function() {
        //     // Function to calculate totals
        //     function calculateTotals() {
        //         const rows = document.querySelectorAll('#product-table-body tr');
        //         let totalItems = 0;
        //         let totalAmount = 0;
        //         let totalDiscountAmount = 0;
        //         let totalBill = 0;

        //         rows.forEach(row => {
        //             const price = parseFloat(row.querySelector('input[name$="[price]"]').value) || 0;
        //             const discount = parseFloat(row.querySelector('input[name$="[discount]"]').value) || 0;

        //             totalItems += 1;
        //             totalAmount += price;
        //             totalDiscountAmount += (price * (discount / 100));
        //             totalBill += (price - (price * (discount / 100)));
        //         });

        //         // Update the fields
        //         document.getElementById('total-items').value = totalItems;
        //         document.getElementById('total-amount').value = totalAmount.toFixed(2);
        //         document.getElementById('total-discount-amount').value = totalDiscountAmount.toFixed(2);
        //         document.getElementById('total-bill').value = totalBill.toFixed(2);
        //     }

        //     // Event listeners for input changes
        //     document.querySelector('#product-table-body').addEventListener('input', function(event) {
        //         if (event.target.matches('input[name$="[price]"]') || event.target.matches('input[name$="[discount]"]')) {
        //             calculateTotals();
        //         }
        //     });

        //     // Initial calculation
        //     calculateTotals();
        // });
    </script>


</body>

</html>