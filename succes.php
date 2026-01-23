<?php
require_once 'includes/config.php';

$order_id = $_GET['order_id'] ?? 0;

if (!$order_id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT o.*, r.name as restaurant_name, r.phone as restaurant_phone
    FROM orders o
    JOIN restaurants r ON o.restaurant_id = r.restaurant_id
    WHERE o.order_id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

// Get order items for display
$stmt = $pdo->prepare("
    SELECT oi.*, mi.name as item_name
    FROM order_items oi
    JOIN menu_items mi ON oi.item_id = mi.item_id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <title>Oda Imethibitishwa - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .success-container-tz {
            max-width: 700px;
            margin: 3rem auto;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .success-header {
            background: linear-gradient(135deg, #117a65 0%, #1abc9c 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }
        
        .success-icon-tz {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: bounce 1s infinite alternate;
        }
        
        @keyframes bounce {
            from { transform: translateY(0); }
            to { transform: translateY(-10px); }
        }
        
        .order-details-tz {
            padding: 2rem;
        }
        
        .detail-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border-left: 5px solid #117a65;
        }
        
        .items-list {
            margin: 1.5rem 0;
        }
        
        .item-row-tz {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem;
            border-bottom: 1px solid #eee;
        }
        
        .whatsapp-action {
            background: #25D366;
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            margin: 1.5rem 0;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .whatsapp-action:hover {
            background: #128C7E;
            transform: translateY(-2px);
        }
        
        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .contact-info {
            background: #e3f2fd;
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 2rem;
        }
        
        .countdown-timer {
            background: #fff3cd;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            margin: 1rem 0;
            font-size: 1.2rem;
        }
        
        .timer-number {
            font-size: 2rem;
            font-weight: bold;
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="success-container-tz">
        <div class="success-header">
            <div class="success-icon-tz">🎉</div>
            <h1>Hongera! Oda Yako Imethibitishwa</h1>
            <p>Asante kwa kuagiza na <?php echo SITE_NAME; ?>. Oda yako inatayarishwa sasa.</p>
        </div>
        
        <div class="order-details-tz">
            <!-- Countdown Timer -->
            <div class="countdown-timer">
                <p>Oda itafika kwenye:</p>
                <div class="timer-number" id="countdown">45:00</div>
                <p>dakika</p>
            </div>
            
            <!-- Order Details -->
            <div class="detail-card">
                <h3><i class="fas fa-info-circle"></i> Maelezo ya Oda</h3>
                <div class="detail-row">
                    <strong>Nambari ya Oda:</strong> <?php echo $order['order_number']; ?>
                </div>
                <div class="detail-row">
                    <strong>Mkahawa:</strong> <?php echo htmlspecialchars($order['restaurant_name']); ?>
                </div>
                <div class="detail-row">
                    <strong>Jumla ya Malipo:</strong> 
                    <span style="font-size: 1.3rem; color: #117a65; font-weight: bold;">
                        <?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($order['total_amount'], 2); ?>
                    </span>
                </div>
                <div class="detail-row">
                    <strong>Njia ya Malipo:</strong> 
                    <span class="badge" style="background: #117a65; color: white; padding: 0.25rem 0.75rem; border-radius: 15px;">
                        <?php 
                        $method_names = [
                            'mpesa' => 'M-Pesa',
                            'tigopesa' => 'Tigo Pesa',
                            'airtelmoney' => 'Airtel Money',
                            'halopesa' => 'HaloPesa',
                            'cod' => 'Pesa Taslimu',
                            'card' => 'Kadi'
                        ];
                        echo $method_names[$order['payment_method']] ?? ucfirst($order['payment_method']);
                        ?>
                    </span>
                </div>
                <div class="detail-row">
                    <strong>Hali ya Malipo:</strong> 
                    <span class="badge <?php echo $order['payment_status'] == 'paid' ? 'badge-success' : 'badge-warning'; ?>">
                        <?php echo $order['payment_status'] == 'paid' ? 'Imelipwa' : 'Inasubiri'; ?>
                    </span>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="detail-card">
                <h3><i class="fas fa-utensils"></i> Vitu Vilivyoagizwa</h3>
                <div class="items-list">
                    <?php foreach($order_items as $item): ?>
                    <div class="item-row-tz">
                        <div>
                            <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                            <div>Idadi: <?php echo $item['quantity']; ?></div>
                        </div>
                        <div><?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- WhatsApp Action -->
            <div class="whatsapp-action" onclick="openOrderWhatsApp()">
                <i class="fab fa-whatsapp"></i> Wasiliana na Mkahawa kupitia WhatsApp
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="order_tracking.php?order_id=<?php echo $order_id; ?>" class="btn" style="background: #117a65;">
                    <i class="fas fa-map-marker-alt"></i> Fuata Oda
                </a>
                <a href="index.php" class="btn" style="background: #7f8c8d;">
                    <i class="fas fa-home"></i> Rudi Nyumbani
                </a>
            </div>
            
            <!-- Contact Information -->
            <div class="contact-info">
                <h4><i class="fas fa-headset"></i> Usaidizi wa Mteja</h4>
                <p><strong>Simu:</strong> +255 787 654 321</p>
                <p><strong>WhatsApp:</strong> +255 787 654 321</p>
                <p><strong>Email:</strong> mteja@chakulaexpress.co.tz</p>
                <p><strong>Masaa:</strong> 08:00 - 22:00 kila siku</p>
            </div>
            
            <!-- Important Notes -->
            <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-top: 1.5rem; font-size: 0.9rem;">
                <p><strong>🔔 Vidokezo Muhimu:</strong></p>
                <ul>
                    <li>Oda inaweza kuchukua dakika 45-60 kufika</li>
                    <li>Wasafirishaji wanaweza kupiga simu kabla ya kufika</li>
                    <li>Kama oda haikufika kwa wakati, wasiliana nasi mara moja</li>
                    <li>Thibitisha anwani kabla ya kuwasilishwa</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script>
        // Countdown Timer
        let countdownTime = 45 * 60; // 45 minutes in seconds
        
        function updateCountdown() {
            const minutes = Math.floor(countdownTime / 60);
            const seconds = countdownTime % 60;
            
            document.getElementById('countdown').textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (countdownTime > 0) {
                countdownTime--;
                setTimeout(updateCountdown, 1000);
            } else {
                document.getElementById('countdown').textContent = '00:00';
                document.querySelector('.countdown-timer').style.background = '#f8d7da';
                document.querySelector('.countdown-timer').innerHTML = 
                    '<p><strong>⏰ Muda umekwisha!</strong></p>' +
                    '<p>Tafadhali wasiliana nasi ikiwa oda haijafika.</p>';
            }
        }
        
        // WhatsApp Integration
        function openOrderWhatsApp() {
            const restaurantPhone = '<?php echo $order["restaurant_phone"]; ?>';
            const orderNumber = '<?php echo $order["order_number"]; ?>';
            const message = `Habari! Ninauliza kuhusu oda yangu #${orderNumber} kutoka Chakula Express.`;
            
            window.open(`https://wa.me/${restaurantPhone}?text=${encodeURIComponent(message)}`, '_blank');
        }
        
        // Auto-start countdown
        document.addEventListener('DOMContentLoaded', function() {
            updateCountdown();
            
            // Send browser notification if supported
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('Oda Imethibitishwa!', {
                    body: 'Oda yako #<?php echo $order["order_number"]; ?> imepokelewa kikamilifu.',
                    icon: 'assets/images/logo.png'
                });
            }
        });
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>