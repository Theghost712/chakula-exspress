<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$order_id = $_GET['order_id'] ?? 0;

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, r.name as restaurant_name, r.phone as restaurant_phone, 
           u.name as customer_name, u.phone as customer_phone
    FROM orders o
    JOIN restaurants r ON o.restaurant_id = r.restaurant_id
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = ? AND (o.user_id = ? OR ? = 'admin')
");
$is_admin = isAdmin() ? 'admin' : '';
$stmt->execute([$order_id, $_SESSION['user_id'], $is_admin]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found or access denied");
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, mi.name as item_name, mi.image_url
    FROM order_items oi
    JOIN menu_items mi ON oi.item_id = mi.item_id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

// Define status steps
$status_steps = [
    'pending' => ['icon' => '⏱️', 'title' => 'Order Placed', 'desc' => 'Restaurant has received your order'],
    'confirmed' => ['icon' => '✅', 'title' => 'Order Confirmed', 'desc' => 'Restaurant is preparing your food'],
    'preparing' => ['icon' => '👨‍🍳', 'title' => 'Food Preparation', 'desc' => 'Your food is being cooked'],
    'ready' => ['icon' => '🎯', 'title' => 'Ready for Pickup', 'desc' => 'Your order is ready for delivery'],
    'out_for_delivery' => ['icon' => '🚚', 'title' => 'Out for Delivery', 'desc' => 'Delivery partner is on the way'],
    'delivered' => ['icon' => '📦', 'title' => 'Delivered', 'desc' => 'Your food has been delivered']
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Track Order #<?php echo $order['order_number']; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .tracking-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
        }
        
        .status-timeline {
            position: relative;
            padding: 2rem 0;
        }
        
        .timeline-line {
            position: absolute;
            left: 30px;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #ddd;
            z-index: 1;
        }
        
        .timeline-line-fill {
            position: absolute;
            left: 30px;
            top: 0;
            height: 0;
            width: 4px;
            background: #4CAF50;
            z-index: 2;
            transition: height 1s ease;
        }
        
        .timeline-step {
            display: flex;
            align-items: center;
            margin-bottom: 2.5rem;
            position: relative;
            z-index: 3;
        }
        
        .step-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: white;
            border: 3px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1.5rem;
            transition: all 0.3s;
        }
        
        .step-icon.active {
            border-color: #4CAF50;
            background: #4CAF50;
            color: white;
        }
        
        .step-icon.completed {
            border-color: #4CAF50;
            background: #4CAF50;
            color: white;
        }
        
        .step-content {
            flex: 1;
        }
        
        .step-title {
            font-weight: bold;
            margin-bottom: 0.25rem;
        }
        
        .step-desc {
            color: #666;
            font-size: 0.9rem;
        }
        
        .order-items {
            margin-top: 2rem;
        }
        
        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem;
            border-bottom: 1px solid #eee;
        }
        
        .eta-badge {
            background: #ff6b35;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            display: inline-block;
            margin-top: 1rem;
        }
        
        .real-time-updates {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
        }
        
        #delivery-person {
            margin-top: 1rem;
            padding: 1rem;
            background: #e8f5e9;
            border-radius: 5px;
            display: none;
        }
        
        .delivery-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        /* Animation for status updates */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .status-updated {
            animation: pulse 0.5s ease-in-out;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="tracking-container">
        <div class="order-header">
            <div>
                <h1>Order #<?php echo $order['order_number']; ?></h1>
                <p>Restaurant: <?php echo htmlspecialchars($order['restaurant_name']); ?></p>
                <p>Placed on: <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
            </div>
            <div class="order-status">
                <span class="status-badge"><?php echo ucfirst($order['order_status']); ?></span>
                <div class="eta-badge">
                    ⏰ ETA: 
                    <?php 
                    $eta = strtotime($order['created_at']) + (30 * 60); // 30 minutes from order time
                    echo date('g:i A', $eta);
                    ?>
                </div>
            </div>
        </div>
        
        <!-- Status Timeline -->
        <div class="status-timeline">
            <div class="timeline-line"></div>
            <div class="timeline-line-fill" id="timelineFill"></div>
            
            <?php 
            $step_index = 0;
            $current_step_index = 0;
            $status_keys = array_keys($status_steps);
            
            foreach($status_steps as $status_key => $step): 
                $is_active = $order['order_status'] == $status_key;
                $is_completed = array_search($status_key, $status_keys) < array_search($order['order_status'], $status_keys);
                
                if ($is_active) $current_step_index = $step_index;
                ?>
                <div class="timeline-step" id="step-<?php echo $status_key; ?>">
                    <div class="step-icon <?php echo $is_active ? 'active' : ($is_completed ? 'completed' : ''); ?>">
                        <?php echo $step['icon']; ?>
                    </div>
                    <div class="step-content">
                        <div class="step-title"><?php echo $step['title']; ?></div>
                        <div class="step-desc"><?php echo $step['desc']; ?></div>
                        <?php if ($is_active): ?>
                            <div class="real-time-updates">
                                <small>🔄 <span id="live-update">Checking for updates...</span></small>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($status_key == 'out_for_delivery' && $order['order_status'] == 'out_for_delivery'): ?>
                            <div id="delivery-person">
                                <strong>🚚 Delivery Partner:</strong>
                                <p>Name: <span id="delivery-name">Assigning...</span></p>
                                <p>Contact: <span id="delivery-contact">--</span></p>
                                <p>Vehicle: <span id="delivery-vehicle">Bike</span></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php 
            $step_index++;
            endforeach; 
            ?>
        </div>
        
        <!-- Order Items -->
        <div class="order-items">
            <h3>Order Summary</h3>
            <?php foreach($order_items as $item): ?>
                <div class="item-row">
                    <div>
                        <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                        <div>Qty: <?php echo $item['quantity']; ?></div>
                    </div>
                    <div>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                </div>
            <?php endforeach; ?>
            
            <div class="item-row" style="border-top: 2px solid #333; font-weight: bold;">
                <div>Total Amount</div>
                <div>₹<?php echo number_format($order['total_amount'], 2); ?></div>
            </div>
        </div>
        
        <!-- Contact & Actions -->
        <div class="delivery-actions">
            <button class="btn" onclick="callRestaurant()">📞 Call Restaurant</button>
            <button class="btn" onclick="cancelOrder()" 
                <?php echo ($order['order_status'] == 'delivered' || $order['order_status'] == 'cancelled') ? 'disabled' : ''; ?>>
                ❌ Cancel Order
            </button>
            <?php if ($order['order_status'] == 'delivered'): ?>
                <button class="btn" onclick="rateOrder()">⭐ Rate Order</button>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Calculate timeline fill height
        document.addEventListener('DOMContentLoaded', function() {
            const steps = document.querySelectorAll('.timeline-step');
            const currentStep = <?php echo $current_step_index; ?>;
            const totalSteps = steps.length;
            
            if (currentStep > 0) {
                const fillHeight = (currentStep / (totalSteps - 1)) * 100;
                document.getElementById('timelineFill').style.height = fillHeight + '%';
            }
            
            // Start real-time updates
            startOrderTracking(<?php echo $order_id; ?>);
        });
        
        // Real-time order tracking with AJAX
        function startOrderTracking(orderId) {
            let lastStatus = '<?php echo $order["order_status"]; ?>';
            
            function checkStatus() {
                fetch(`api/check_order_status.php?order_id=${orderId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status && data.status !== lastStatus) {
                            // Status changed - update UI
                            updateOrderStatus(data.status);
                            lastStatus = data.status;
                            
                            // Show notification
                            showNotification(`Order status updated to: ${data.status}`, 'success');
                        }
                        
                        // Update live update text
                        document.getElementById('live-update').textContent = 
                            `Last checked: ${new Date().toLocaleTimeString()}`;
                    })
                    .catch(error => {
                        console.error('Error checking status:', error);
                    });
            }
            
            // Check every 10 seconds
            setInterval(checkStatus, 10000);
            
            // First check
            checkStatus();
        }
        
        function updateOrderStatus(newStatus) {
            // Update all steps
            const steps = document.querySelectorAll('.timeline-step');
            const statusKeys = <?php echo json_encode(array_keys($status_steps)); ?>;
            const newIndex = statusKeys.indexOf(newStatus);
            
            steps.forEach((step, index) => {
                const icon = step.querySelector('.step-icon');
                icon.classList.remove('active', 'completed');
                
                if (index < newIndex) {
                    icon.classList.add('completed');
                } else if (index === newIndex) {
                    icon.classList.add('active');
                    icon.classList.add('status-updated');
                    
                    // Remove animation class after animation completes
                    setTimeout(() => {
                        icon.classList.remove('status-updated');
                    }, 500);
                }
            });
            
            // Update timeline fill
            const fillHeight = (newIndex / (steps.length - 1)) * 100;
            document.getElementById('timelineFill').style.height = fillHeight + '%';
            
            // Show/hide delivery person info
            const deliveryDiv = document.getElementById('delivery-person');
            if (deliveryDiv) {
                deliveryDiv.style.display = newStatus === 'out_for_delivery' ? 'block' : 'none';
                if (newStatus === 'out_for_delivery') {
                    // Simulate delivery partner assignment
                    setTimeout(() => {
                        document.getElementById('delivery-name').textContent = 'Rahul Sharma';
                        document.getElementById('delivery-contact').textContent = '+91 98765 43210';
                    }, 2000);
                }
            }
        }
        
        function callRestaurant() {
            const phone = '<?php echo $order["restaurant_phone"]; ?>';
            window.location.href = `tel:${phone}`;
        }
        
        function cancelOrder() {
            if (confirm('Are you sure you want to cancel this order?')) {
                fetch(`api/cancel_order.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: <?php echo $order_id; ?>,
                        reason: prompt('Please provide reason for cancellation:')
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Order cancelled successfully', 'success');
                        updateOrderStatus('cancelled');
                    } else {
                        showNotification(data.message, 'error');
                    }
                });
            }
        }
        
        function rateOrder() {
            window.location.href = `rate_order.php?order_id=<?php echo $order_id; ?>`;
        }
        
        function showNotification(message, type) {
            // Your notification implementation
            alert(message);
        }
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>