<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Get cart items
$stmt = $pdo->prepare("
    SELECT c.*, mi.name, mi.price, r.name as restaurant_name, r.delivery_fee
    FROM cart c
    JOIN menu_items mi ON c.item_id = mi.item_id
    JOIN restaurants r ON c.restaurant_id = r.restaurant_id
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    header("Location: cart.php");
    exit;
}

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$deliveryFee = $cartItems[0]['delivery_fee'];
$vat = $subtotal * VAT_RATE; // 18% VAT
$service_fee = 500; // Fixed service fee in TSh
$total = $subtotal + $deliveryFee + $vat + $service_fee;

// Save order in session
$_SESSION['pending_order'] = [
    'restaurant_id' => $cartItems[0]['restaurant_id'],
    'subtotal' => $subtotal,
    'delivery_fee' => $deliveryFee,
    'vat' => $vat,
    'service_fee' => $service_fee,
    'total' => $total,
    'items' => $cartItems
];
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <title>Malipo - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .checkout-container {
            max-width: 1000px;
            margin: 2rem auto;
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
        }
        
        .payment-methods-tz {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
        }
        
        .payment-option-tz {
            border: 2px solid #ddd;
            padding: 1.2rem;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        
        .payment-option-tz:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .payment-option-tz.selected {
            border-color: #117a65;
            background: #e8f6f3;
        }
        
        .payment-icon-tz {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            height: 60px;
        }
        
        .payment-icon-tz img {
            height: 50px;
            object-fit: contain;
        }
        
        .payment-form {
            display: none;
            margin: 1.5rem 0;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 10px;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .phone-input-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .country-code {
            padding: 0.8rem;
            background: #eee;
            border-radius: 5px;
            font-weight: bold;
        }
        
        .amount-breakdown-tz {
            background: #e8f6f3;
            padding: 1.5rem;
            border-radius: 10px;
            margin: 1.5rem 0;
        }
        
        .breakdown-row-tz {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px dashed #bdc3c7;
        }
        
        .breakdown-row-tz.total {
            border-top: 2px solid #117a65;
            border-bottom: none;
            font-size: 1.3rem;
            font-weight: bold;
            color: #117a65;
            padding-top: 1rem;
            margin-top: 0.5rem;
        }
        
        .mpesa-steps {
            background: #e1f5fe;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
        
        .step-number {
            display: inline-block;
            width: 25px;
            height: 25px;
            background: #0288d1;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 25px;
            margin-right: 10px;
        }
        
        .payment-help {
            background: #fff8e1;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
        
        .whatsapp-help {
            background: #25D366;
            color: white;
            padding: 0.8rem;
            border-radius: 5px;
            text-align: center;
            margin-top: 1rem;
            cursor: pointer;
        }
        
        .whatsapp-help:hover {
            background: #128C7E;
        }
        
        .payment-status {
            display: none;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
            text-align: center;
        }
        
        .status-checking {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="checkout-container">
        <!-- Left Column: Delivery & Payment -->
        <div>
            <h2><i class="fas fa-map-marker-alt"></i> Maelezo ya Uwasilishaji</h2>
            
            <form id="deliveryForm">
                <div class="form-group">
                    <label>Anwani ya Uwasilishaji *</label>
                    <textarea name="address" rows="3" required 
                              placeholder="Ingiza anwani kamili ya uwasilishaji"><?php 
                              echo $_SESSION['user_address'] ?? ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Jiji *</label>
                    <select name="city" required>
                        <option value="">Chagua Jiji</option>
                        <option value="Dar es Salaam" selected>Dar es Salaam</option>
                        <option value="Dodoma">Dodoma</option>
                        <option value="Arusha">Arusha</option>
                        <option value="Mwanza">Mwanza</option>
                        <option value="Mbeya">Mbeya</option>
                        <option value="Morogoro">Morogoro</option>
                        <option value="Tanga">Tanga</option>
                        <option value="Zanzibar">Zanzibar</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Nambari ya Simu *</label>
                    <div class="phone-input-group">
                        <span class="country-code">+255</span>
                        <input type="tel" name="phone" pattern="[0-9]{9}" 
                               placeholder="712345678" required
                               value="<?php echo substr($_SESSION['user_phone'] ?? '', 3); ?>">
                    </div>
                    <small>Weka nambari ya simu itakayotumika kwa mawasiliano</small>
                </div>
                
                <div class="form-group">
                    <label>Maagizo ya Ziada (Hiari)</label>
                    <textarea name="instructions" rows="2" 
                              placeholder="Mfano: Wito kabla ya kufika, Acha mlangoni, n.k."></textarea>
                </div>
            </form>
            
            <!-- Tanzanian Payment Methods -->
            <h2><i class="fas fa-money-bill-wave"></i> Chagua Njia ya Malipo</h2>
            
            <div class="payment-methods-tz">
                <!-- M-Pesa -->
                <div class="payment-option-tz" onclick="selectPaymentTZ('mpesa')">
                    <div class="payment-icon-tz">
                        <img src="assets/images/mpesa-logo.png" alt="M-Pesa">
                    </div>
                    <div>M-Pesa</div>
                    <small>Lipa kwa M-Pesa</small>
                </div>
                
                <!-- Tigo Pesa -->
                <div class="payment-option-tz" onclick="selectPaymentTZ('tigopesa')">
                    <div class="payment-icon-tz">
                        <img src="assets/images/tigopesa-logo.png" alt="Tigo Pesa">
                    </div>
                    <div>Tigo Pesa</div>
                    <small>Lipa kwa Tigo Pesa</small>
                </div>
                
                <!-- Airtel Money -->
                <div class="payment-option-tz" onclick="selectPaymentTZ('airtelmoney')">
                    <div class="payment-icon-tz">
                        <img src="assets/images/airtel-money-logo.png" alt="Airtel Money">
                    </div>
                    <div>Airtel Money</div>
                    <small>Lipa kwa Airtel Money</small>
                </div>
                
                <!-- HaloPesa -->
                <div class="payment-option-tz" onclick="selectPaymentTZ('halopesa')">
                    <div class="payment-icon-tz">
                        <img src="assets/images/halopesa-logo.png" alt="HaloPesa">
                    </div>
                    <div>HaloPesa</div>
                    <small>Lipa kwa HaloPesa</small>
                </div>
                
                <!-- Cash on Delivery -->
                <div class="payment-option-tz" onclick="selectPaymentTZ('cod')">
                    <div class="payment-icon-tz">💵</div>
                    <div>Cash on Delivery</div>
                    <small>Lipa unapopokea</small>
                </div>
                
                <!-- Card Payment -->
                <div class="payment-option-tz" onclick="selectPaymentTZ('card')">
                    <div class="payment-icon-tz">💳</div>
                    <div>Kadi ya Benki</div>
                    <small>Visa/MasterCard</small>
                </div>
            </div>
            
            <!-- Payment Forms -->
            <div id="mpesaForm" class="payment-form">
                <h4><img src="assets/images/mpesa-logo.png" alt="M-Pesa" style="height: 30px; vertical-align: middle;"> Malipo ya M-Pesa</h4>
                
                <div class="form-group">
                    <label>Weka Nambari ya M-Pesa *</label>
                    <div class="phone-input-group">
                        <span class="country-code">+255</span>
                        <input type="tel" id="mpesaPhone" pattern="[0-9]{9}" 
                               placeholder="712345678" required>
                    </div>
                    <small>Hakikisha nambari hii iko kwenye kifurushi cha data</small>
                </div>
                
                <div class="mpesa-steps">
                    <p><span class="step-number">1</span> Utapokea ujumbe wa kuthibitisha kutoka kwa M-Pesa</p>
                    <p><span class="step-number">2</span> Ingiza nambari ya siri ya M-Pesa yako</p>
                    <p><span class="step-number">3</span> Nenda kwenye LIPA NA M-PESA &gt; Weka PIN</p>
                    <p><span class="step-number">4</span> Thibitisha malipo</p>
                </div>
                
                <button type="button" class="btn" style="background: #00A656;" onclick="initiateMpesaPayment()">
                    <i class="fas fa-paper-plane"></i> Tuma Malipo ya M-Pesa
                </button>
                
                <div id="mpesaStatus" class="payment-status"></div>
            </div>
            
            <div id="tigopesaForm" class="payment-form">
                <h4>Tigo Pesa Payment</h4>
                <!-- Similar form for Tigo Pesa -->
            </div>
            
            <div id="airtelmoneyForm" class="payment-form">
                <h4>Airtel Money Payment</h4>
                <!-- Similar form for Airtel Money -->
            </div>
            
            <div id="halopesaForm" class="payment-form">
                <h4>HaloPesa Payment</h4>
                <!-- Similar form for HaloPesa -->
            </div>
            
            <div id="codForm" class="payment-form">
                <h4>Cash on Delivery</h4>
                <div class="payment-help">
                    <p><strong>💵 Malipo Unapopokea</strong></p>
                    <p>Utalipa pesa taslimu unapopokea oda yako. Tafadhali tayarisha pesa taslimu.</p>
                    <p>Ada ya ziada ya TSh 500 inaweza kutozwa kwa ajili ya huduma ya COD.</p>
                </div>
            </div>
            
            <div id="cardForm" class="payment-form">
                <h4>Card Payment</h4>
                <!-- Card payment form -->
            </div>
            
            <!-- WhatsApp Help -->
            <div class="whatsapp-help" onclick="openWhatsApp()">
                <i class="fab fa-whatsapp"></i> Una shida? Wasiliana na sisi kupitia WhatsApp
            </div>
        </div>
        
        <!-- Right Column: Order Summary -->
        <div class="order-summary">
            <h3><i class="fas fa-receipt"></i> Muhtasari wa Oda</h3>
            
            <div class="amount-breakdown-tz">
                <div class="breakdown-row-tz">
                    <span>Jumla ya Bidhaa</span>
                    <span><?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="breakdown-row-tz">
                    <span>Ada ya Uwasilishaji</span>
                    <span><?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($deliveryFee, 2); ?></span>
                </div>
                <div class="breakdown-row-tz">
                    <span>VAT (<?php echo (VAT_RATE * 100); ?>%)</span>
                    <span><?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($vat, 2); ?></span>
                </div>
                <div class="breakdown-row-tz">
                    <span>Ada ya Huduma</span>
                    <span><?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($service_fee, 2); ?></span>
                </div>
                <div class="breakdown-row-tz total">
                    <span>JUMLA YA KULIPA</span>
                    <span><?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($total, 2); ?></span>
                </div>
            </div>
            
            <div id="selectedPaymentInfo">
                <!-- Selected payment info will appear here -->
            </div>
            
            <button id="placeOrderBtn" class="btn" style="width: 100%; padding: 1rem; font-size: 1.1rem; background: #117a65;" 
                    onclick="placeOrderTZ()" disabled>
                <i class="fas fa-check-circle"></i> THIBITISHA ODA
            </button>
            
            <p style="text-align: center; margin-top: 1rem; font-size: 0.9rem; color: #666;">
                Kwa kubonyeza, unakubali <a href="terms.php">Masharti na Vigezo</a> yetu
            </p>
        </div>
    </div>
    
    <script>
        let selectedPaymentMethod = '';
        let paymentInProgress = false;
        
        function selectPaymentTZ(method) {
            selectedPaymentMethod = method;
            
            // Update UI
            document.querySelectorAll('.payment-option-tz').forEach(opt => {
                opt.classList.remove('selected');
            });
            event.target.closest('.payment-option-tz').classList.add('selected');
            
            // Hide all forms
            document.querySelectorAll('.payment-form').forEach(form => {
                form.style.display = 'none';
            });
            
            // Show selected form
            document.getElementById(method + 'Form').style.display = 'block';
            
            // Update payment info
            updatePaymentInfo(method);
            
            // Enable place order button
            document.getElementById('placeOrderBtn').disabled = false;
        }
        
        function updatePaymentInfo(method) {
            const methodNames = {
                'mpesa': 'M-Pesa',
                'tigopesa': 'Tigo Pesa',
                'airtelmoney': 'Airtel Money',
                'halopesa': 'HaloPesa',
                'cod': 'Cash on Delivery',
                'card': 'Kadi ya Benki'
            };
            
            const infoDiv = document.getElementById('selectedPaymentInfo');
            infoDiv.innerHTML = `
                <div style="background: #e8f6f3; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <h4>Njia ya Malipo: ${methodNames[method]}</h4>
                    <p>Jumla ya kulipa: <strong><?php echo CURRENCY_SYMBOL; ?> ${<?php echo $total; ?>.toLocaleString()}</strong></p>
                    ${method === 'cod' ? '<p><small>📝 Bili itatolewa wakati wa uwasilishaji</small></p>' : ''}
                </div>
            `;
        }
        
        function initiateMpesaPayment() {
            const phone = document.getElementById('mpesaPhone').value;
            if (!phone || phone.length !== 9) {
                alert('Tafadhali weka nambari sahihi ya simu (tarakimu 9)');
                return;
            }
            
            const fullPhone = '+255' + phone;
            const amount = <?php echo $total; ?>;
            
            // Disable button and show status
            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Inatumwa...';
            
            const statusDiv = document.getElementById('mpesaStatus');
            statusDiv.className = 'payment-status status-checking';
            statusDiv.innerHTML = 'Inatuma ombi la malipo kwa M-Pesa...';
            statusDiv.style.display = 'block';
            
            // Send payment request
            fetch('api/initiate_mpesa.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    phone: fullPhone,
                    amount: amount,
                    order_data: <?php echo json_encode($_SESSION['pending_order']); ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusDiv.className = 'payment-status status-success';
                    statusDiv.innerHTML = `
                        <i class="fas fa-check-circle"></i> Ombi limewasilishwa!<br>
                        <small>Angalia simu yako kwa ujumbe wa M-Pesa na weka PIN yako.</small><br>
                        <small>Nambari ya rejea: ${data.reference}</small>
                    `;
                    
                    // Start checking payment status
                    checkMpesaPaymentStatus(data.transaction_id);
                    
                    // Enable place order button
                    document.getElementById('placeOrderBtn').disabled = false;
                    
                } else {
                    statusDiv.className = 'payment-status status-failed';
                    statusDiv.innerHTML = `<i class="fas fa-times-circle"></i> ${data.message}`;
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Jaribu Tena';
                }
            })
            .catch(error => {
                statusDiv.className = 'payment-status status-failed';
                statusDiv.innerHTML = '<i class="fas fa-times-circle"></i> Hitilafu ya mtandao. Tafadhali jaribu tena.';
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Jaribu Tena';
            });
        }
        
        function checkMpesaPaymentStatus(transactionId) {
            const statusDiv = document.getElementById('mpesaStatus');
            
            function check() {
                fetch(`api/check_mpesa_status.php?transaction_id=${transactionId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'completed') {
                            statusDiv.innerHTML = `
                                <i class="fas fa-check-circle"></i> Malipo yamethibitishwa!<br>
                                <small>Nambari ya muamala: ${data.transaction_code}</small>
                            `;
                            // Auto-proceed to place order
                            setTimeout(() => {
                                placeOrderTZ();
                            }, 2000);
                        } else if (data.status === 'failed') {
                            statusDiv.innerHTML = `<i class="fas fa-times-circle"></i> Malipo yameshindikana.`;
                        } else {
                            // Still pending, check again in 5 seconds
                            setTimeout(check, 5000);
                        }
                    });
            }
            
            // Start checking
            setTimeout(check, 10000); // Wait 10 seconds before first check
        }
        
        function placeOrderTZ() {
            if (paymentInProgress) return;
            
            // Validate delivery form
            const form = document.getElementById('deliveryForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            // For COD, just submit order
            if (selectedPaymentMethod === 'cod') {
                submitOrderTZ();
                return;
            }
            
            // For mobile money, check if payment was initiated
            const mobileMoneyMethods = ['mpesa', 'tigopesa', 'airtelmoney', 'halopesa'];
            if (mobileMoneyMethods.includes(selectedPaymentMethod)) {
                const statusDiv = document.getElementById(selectedPaymentMethod + 'Status');
                if (!statusDiv || !statusDiv.innerHTML.includes('Malipo yamethibitishwa')) {
                    alert('Tafadhali kamilisha malipo ya ' + selectedPaymentMethod + ' kabla ya kuendelea.');
                    return;
                }
            }
            
            paymentInProgress = true;
            const btn = document.getElementById('placeOrderBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Inaendelea...';
            
            submitOrderTZ();
        }
        
        function submitOrderTZ() {
            const form = document.getElementById('deliveryForm');
            const formData = new FormData(form);
            
            const orderData = {
                payment_method: selectedPaymentMethod,
                address: formData.get('address'),
                city: formData.get('city'),
                phone: '+255' + formData.get('phone'),
                instructions: formData.get('instructions')
            };
            
            // For mobile money, add phone number
            if (selectedPaymentMethod === 'mpesa') {
                orderData.mpesa_phone = '+255' + document.getElementById('mpesaPhone').value;
            }
            
            fetch('api/place_order_tz.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to success page
                    window.location.href = `order_success.php?order_id=${data.order_id}`;
                } else {
                    alert('Hitilafu: ' + data.message);
                    document.getElementById('placeOrderBtn').disabled = false;
                    document.getElementById('placeOrderBtn').innerHTML = '<i class="fas fa-check-circle"></i> THIBITISHA ODA';
                    paymentInProgress = false;
                }
            })
            .catch(error => {
                alert('Hitilafu ya mtandao. Tafadhali jaribu tena.');
                document.getElementById('placeOrderBtn').disabled = false;
                document.getElementById('placeOrderBtn').innerHTML = '<i class="fas fa-check-circle"></i> THIBITISHA ODA';
                paymentInProgress = false;
            });
        }
        
        function openWhatsApp() {
            const phone = '255787654321'; // Your support number
            const message = 'Habari, naomba usaidizi kuhusu malipo kwenye Chakula Express';
            window.open(`https://wa.me/${phone}?text=${encodeURIComponent(message)}`, '_blank');
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-select M-Pesa by default
            document.querySelector('.payment-option-tz').click();
            
            // Format phone inputs
            document.querySelectorAll('input[type="tel"]').forEach(input => {
                input.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 9) value = value.slice(0, 9);
                    e.target.value = value;
                });
            });
        });
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>