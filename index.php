<?php
require_once '../includes/config.php';

if (!isAdmin()) {
    header("Location: ../login.php");
    exit;
}

// Get stats
$stats = [];
$queries = [
    'total_orders' => "SELECT COUNT(*) as count FROM orders",
    'total_users' => "SELECT COUNT(*) as count FROM users WHERE user_type = 'customer'",
    'total_restaurants' => "SELECT COUNT(*) as count FROM restaurants",
    'today_orders' => "SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()",
    'total_revenue' => "SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'",
    'pending_orders' => "SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'"
];

foreach ($queries as $key => $query) {
    $stmt = $pdo->query($query);
    $stats[$key] = $stmt->fetch()['count'] ?? 0;
}

// Recent orders
$stmt = $pdo->query("
    SELECT o.*, u.name as customer_name, r.name as restaurant_name
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    JOIN restaurants r ON o.restaurant_id = r.restaurant_id
    ORDER BY o.created_at DESC
    LIMIT 10
");
$recent_orders = $stmt->fetchAll();

// Recent users
$stmt = $pdo->query("
    SELECT * FROM users 
    WHERE user_type = 'customer'
    ORDER BY created_at DESC 
    LIMIT 5
");
$recent_users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 1rem 0;
        }
        
        .sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid #34495e;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }
        
        .sidebar-menu li {
            margin: 0;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 1rem;
            color: #ecf0f1;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: #34495e;
            border-left: 4px solid #3498db;
        }
        
        .sidebar-menu i {
            width: 20px;
            margin-right: 10px;
        }
        
        .main-content {
            flex: 1;
            background: #f5f6fa;
            padding: 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.5rem;
            color: white;
        }
        
        .stat-info h3 {
            margin: 0;
            font-size: 1.8rem;
            color: #2c3e50;
        }
        
        .stat-info p {
            margin: 0;
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .chart-container {
            height: 300px;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-utensils"></i> FoodExpress Admin</h2>
                <small>Welcome, <?php echo $_SESSION['name']; ?></small>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="restaurants.php"><i class="fas fa-store"></i> Restaurants</a></li>
                <li><a href="menu.php"><i class="fas fa-utensils"></i> Menu Items</a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                <li><a href="reviews.php"><i class="fas fa-star"></i> Reviews</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <h1>Dashboard Overview</h1>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #3498db;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_orders']); ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #2ecc71;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_users']); ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e74c3c;">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_restaurants']); ?></h3>
                        <p>Restaurants</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f39c12;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3>₹<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #9b59b6;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['today_orders']); ?></h3>
                        <p>Today's Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #1abc9c;">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['pending_orders']); ?></h3>
                        <p>Pending Orders</p>
                    </div>
                </div>
            </div>
            
            <!-- Charts and Tables -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                <!-- Recent Orders -->
                <div class="card">
                    <div class="card-header">
                        <h3>Recent Orders</h3>
                        <a href="orders.php" class="btn btn-sm">View All</a>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Restaurant</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_orders as $order): ?>
                                <tr>
                                    <td><?php echo $order['order_number']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['restaurant_name']); ?></td>
                                    <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                            <?php echo ucfirst($order['order_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="../order_tracking.php?order_id=<?php echo $order['order_id']; ?>" 
                                           class="btn btn-sm" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if($order['order_status'] == 'pending'): ?>
                                            <button onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'confirmed')" 
                                                    class="btn btn-sm">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Recent Users -->
                <div class="card">
                    <div class="card-header">
                        <h3>Recent Users</h3>
                        <a href="users.php" class="btn btn-sm">View All</a>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header">
                    <h3>Orders Chart (Last 7 Days)</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="ordersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Orders Chart
        const ctx = document.getElementById('ordersChart').getContext('2d');
        const ordersChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Orders',
                    data: [12, 19, 15, 25, 22, 30, 35],
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Update Order Status
        function updateOrderStatus(orderId, status) {
            if (confirm('Update order status to ' + status + '?')) {
                fetch('api/update_order_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: orderId,
                        status: status
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Order status updated successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
        
        // Real-time updates for dashboard
        function updateDashboardStats() {
            fetch('api/get_dashboard_stats.php')
                .then(response => response.json())
                .then(data => {
                    // Update stats if needed
                    console.log('Dashboard stats updated', data);
                });
        }
        
        // Update every 30 seconds
        setInterval(updateDashboardStats, 30000);
    </script>
</body>
</html>