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
        <?php if(session('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
        <div class="alert alert-danger">
            <?php echo e(session('error')); ?>

        </div>
        <?php endif; ?>

        <form id="invoice-form" action="<?php echo e(route('invoice.save')); ?>" method="POST" data-parsley-validate>
            <?php echo csrf_field(); ?>

            <?php if(isset($customer) && $customer): ?>
            <input type="hidden" name="is_existing" value="1">
            <input type="hidden" name="customer_id" value="<?php echo e($customer->customer_id); ?>">
            <?php else: ?>
            <input type="hidden" name="is_existing" value="0">
            <?php endif; ?>
            <div class="card">
                <div class="card-body">
                    <div class="form-group">

                        <label for="customer_name">Customer Name</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name"
                            value="<?php echo e(old('customer_name',$customer->customer_name)); ?>"
                            required data-parsley-required-message="Please enter the customer name">
                    </div>

                    <div class="form-group">
                        <label for="customer_email">Customer Email</label>
                        <input type="email" class="form-control" id="customer_email" name="customer_email"
                            value="<?php echo e(old('customer_email', $customer->customer_email)); ?>"
                            required data-parsley-required-message="Please enter your email address"
                            data-parsley-type="email" data-parsley-type-message="Please enter a valid email address">
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
                            <input type="hidden" name="total_items" id="hidden-total-items">
                        </div>
                        <div class="form-group">
                            <label>Total Amount</label>
                            <input type="number" class="form-control" id="total-amount" readonly>
                            <input type="hidden" name="total_amount" id="hidden-total-amount">
                        </div>
                        <div class="form-group">
                            <label>Total Discount Amount</label>
                            <input type="text" class="form-control" id="total-discount-amount" readonly>
                            <input type="hidden" name="total_discount_amount" id="hidden-total-discount-amount">
                        </div>
                        <div class="form-group">
                            <label>Total Bill</label>
                            <input type="text" class="form-control" id="total-bill" readonly>
                            <input type="hidden" name="total_bill" id="hidden-total-bill">
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

            function updateTotals() {
                let totalItems = $('#product-table-body tr').length;
                let totalAmount = 0;
                let totalDiscount = 0;

                $('#product-table-body tr').each(function() {
                    let price = parseFloat($(this).find('input[name$="[price]"]').val()) || 0;
                    let discount = parseFloat($(this).find('input[name$="[discount]"]').val()) || 0;
                    totalAmount += price;
                    totalDiscount += (price * discount / 100);
                });

                let totalBill = totalAmount - totalDiscount;

                $('#total-items').val(totalItems);
                $('#total-amount').val(totalAmount.toFixed(2));
                $('#total-discount-amount').val(totalDiscount.toFixed(2));
                $('#total-bill').val(totalBill.toFixed(2));

                $('#hidden-total-items').val(totalItems);
                $('#hidden-total-amount').val(totalAmount.toFixed(2));
                $('#hidden-total-discount-amount').val(totalDiscount.toFixed(2));
                $('#hidden-total-bill').val(totalBill.toFixed(2));
            }

            $('#add-product-btn').on('click', function() {
                let newRow = `
        <tr>
            <td>
                <input type="text" class="form-control" name="products[${productCount}][name]" required data-parsley-required-message="Please enter product name">
            </td>
            <td>
                <input type="number" class="form-control" name="products[${productCount}][price]" step="0.01" required data-parsley-required-message="Please enter price" data-parsley-min="0.01" data-parsley-min-message="Price must be greater than zero">
            </td>
            <td>
                <input type="number" class="form-control" name="products[${productCount}][discount]" step="0.01">
            </td>
        </tr>`;
                $('#product-table-body').append(newRow);
                productCount++;
                updateTotals();
            });

            $('#product-table-body').on('input', 'input[name$="[price]"], input[name$="[discount]"]', function() {
                updateTotals();
            });

            $('#invoice-form').on('submit', function() {
                updateTotals();
            });

            $('#invoice-form').parsley();
        });


        // for add product boxes 
        document.addEventListener('DOMContentLoaded', function() {
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
                document.getElementById('total-items').value = totalItems;
                document.getElementById('total-amount').value = totalAmount.toFixed(2);
                document.getElementById('total-discount-amount').value = totalDiscountAmount.toFixed(2);
                document.getElementById('total-bill').value = totalBill.toFixed(2);
            }
            document.querySelector('#product-table-body').addEventListener('input', function(event) {
                if (event.target.matches('input[name$="[price]"]') || event.target.matches('input[name$="[discount]"]')) {
                    calculateTotals();
                }
            });
            calculateTotals();
        });
    </script>

</body>

</html><?php /**PATH /opt/lampp/htdocs/testProject/resources/views/invoice/create.blade.php ENDPATH**/ ?>