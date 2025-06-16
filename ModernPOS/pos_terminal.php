<?php
require __DIR__ . '/templates/header.php';
require __DIR__ . '/templates/sidebar.php';
$products = $pdo->query("SELECT * FROM products WHERE quantity > 0 AND is_active = 1 ORDER BY name")->fetchAll();
?>

<div class="pos-container">
    <div class="product-selection">
        <input type="text" id="product-search" class="form-control" placeholder="Search by Name or Barcode (SKU)...">
        <div class="product-list" id="product-list">
             <?php foreach ($products as $product): ?>
                <div class="product-card" data-id="<?php echo $product['id']; ?>" data-name="<?php echo htmlspecialchars($product['name']); ?>" data-price="<?php echo $product['price']; ?>" data-sku="<?php echo htmlspecialchars($product['sku']); ?>">
                    <img src="uploads/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <div class="product-card-name"><?php echo htmlspecialchars($product['name']); ?></div>
                    <div class="product-card-sku"><?php echo htmlspecialchars($product['sku']); ?></div>
                    <div class="product-card-price">$<?php echo number_format($product['price'], 2); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="cart-section">
        <div class="customer-section">
            <h4><i class="fa-solid fa-user"></i> Customer</h4>
            <input type="search" id="customer-search" class="form-control" placeholder="Search by Phone or Name...">
            <div id="customer-results"></div>
            <div id="selected-customer"><span id="customer-name">General Customer</span></div>
            <button id="add-customer-btn" class="btn btn-primary" style="display:none; width:100%; margin-top:10px;">Register New Customer</button>
        </div>
        <h3><i class="fa-solid fa-shopping-cart"></i> Current Sale</h3>
        <div class="cart-items" id="cart-items"><p>Select products from the left to begin.</p></div>
        <div class="cart-total">Total: <span id="cart-total">$0.00</span></div>
        <button id="complete-sale-btn" class="btn btn-success"><i class="fa-solid fa-check-circle"></i> Complete Sale</button>
    </div>
</div>

<div id="confirmation-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Confirm Sale</h2>
        <div id="modal-cart-summary">
            </div>
        <div class="modal-total">
            <strong>Grand Total: <span id="modal-grand-total">$0.00</span></strong>
        </div>
        <hr>
        <h4>Select Payment Method</h4>
        <div class="payment-methods">
            <button class="payment-method-btn active" data-method="cash"><i class="fa-solid fa-money-bill-wave"></i> Cash</button>
            <button class="payment-method-btn" data-method="card"><i class="fa-solid fa-credit-card"></i> Card</button>
            <button class="payment-method-btn" data-method="qr"><i class="fa-solid fa-qrcode"></i> QR Code</button>
        </div>
        <div id="bank-selection" style="display: none; margin-top: 15px;">
            <div class="form-group">
                <label for="payment-provider">Select Bank</label>
                <select id="payment-provider" class="form-control">
                    <option value="ABA Bank">ABA Bank</option>
                    <option value="Wing Bank">Wing Bank</option>
                    <option value="ACLEDA Bank">ACLEDA Bank</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>
        <button id="confirm-pay-btn" class="btn btn-success" style="width:100%; margin-top: 20px; padding: 15px; font-size: 1.2em;">Confirm & Pay</button>
    </div>
</div>

<?php 
$page_scripts = ['assets/js/pos.js'];
require __DIR__ . '/templates/footer.php'; 
?>