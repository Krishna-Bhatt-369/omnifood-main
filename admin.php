<?php
session_start();
include 'connect.php';

// --- 1. SECURITY / LOGOUT ---
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// --- 2. ACTIONS ---

// DELETE User/Order
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM cafe WHERE id = $id");
    
    // Maintain filter if active
    $redirect = "admin.php?msg=deleted";
    if(isset($_GET['email_filter'])) {
        $redirect .= "&email_filter=" . urlencode($_GET['email_filter']);
    }
    header("Location: $redirect");
    exit;
}

// APPROVE PAYMENT
if (isset($_GET['approve_payment'])) {
    $id = intval($_GET['approve_payment']);
    $conn->query("UPDATE cafe SET payment_status = 'paid', status = 'read' WHERE id = $id");
    
    $redirect = "admin.php?msg=approved";
    if(isset($_GET['email_filter'])) {
        $redirect .= "&email_filter=" . urlencode($_GET['email_filter']);
    }
    header("Location: $redirect");
    exit;
}

// MARK AS COMPLETED
if (isset($_GET['complete_order'])) {
    $id = intval($_GET['complete_order']);
    $conn->query("UPDATE cafe SET status = 'completed' WHERE id = $id");
    
    $redirect = "admin.php?msg=completed";
    if(isset($_GET['email_filter'])) {
        $redirect .= "&email_filter=" . urlencode($_GET['email_filter']);
    }
    header("Location: $redirect");
    exit;
}

// MARK ALL AS READ (Notification Bell)
if (isset($_GET['mark_read_all'])) {
    $conn->query("UPDATE cafe SET status = 'read' WHERE status = 'new'");
    header("Location: admin.php");
    exit;
}

// --- 3. EXPORT CSV ---
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="omnifood_data.csv"');
    $output = fopen('php://output', 'w');
    
    // Added 'Phone' to the header row
    fputcsv($output, array('ID', 'Name', 'Phone', 'Email', 'Order Items', 'Payment Status', 'Status', 'Date'));
    
    $rows = $conn->query("SELECT * FROM cafe ORDER BY id DESC");
    while ($row = $rows->fetch_assoc()) {
        $name = !empty($row['Name']) ? $row['Name'] : "Guest"; 
        // Fetch phone from DB column
        $phone = !empty($row['phone_number']) ? $row['phone_number'] : "N/A";
        $pay_stat = isset($row['payment_status']) ? $row['payment_status'] : 'none';
        
        fputcsv($output, array($row['id'], $name, $phone_number, $row['Email'], $row['order_items'], $pay_stat, $row['status'], $row['created_at']));
    }
    fclose($output);
    exit;
}

// --- 4. FETCH STATS ---
$notif_res = $conn->query("SELECT COUNT(*) as count FROM cafe WHERE status = 'new'");
$new_count = $notif_res->fetch_assoc()['count'];

$pending_res = $conn->query("SELECT COUNT(*) as count FROM cafe WHERE payment_status = 'pending'");
$pending_count = $pending_res->fetch_assoc()['count'];

$total_res = $conn->query("SELECT COUNT(*) as count FROM cafe");
$total_count = $total_res->fetch_assoc()['count'];

// --- 5. DATA FETCHING (WITH FILTER LOGIC) ---
$filter_email = "";
$result_list = null;

if (isset($_GET['email_filter']) && !empty($_GET['email_filter'])) {
    // Show only orders from specific email
    $filter_email = urldecode($_GET['email_filter']);
    $stmt = $conn->prepare("SELECT * FROM cafe WHERE email = ? ORDER BY id DESC");
    $stmt->bind_param("s", $filter_email);
    $stmt->execute();
    $result_list = $stmt->get_result();
} else {
    // Default view (Limit 50 for performance)
    $result_list = $conn->query("SELECT * FROM cafe ORDER BY id DESC LIMIT 50");
}

$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Omnifood</title>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <style>
        :root { --primary: #e67e22; --dark: #333; --bg: #fdf2e9; }
        body { font-family: 'Rubik', sans-serif; margin: 0; background-color: #f9f9f9; display: flex; height: 100vh; }
        
        /* SIDEBAR */
        .sidebar { width: 260px; background: #2e2e2e; color: white; display: flex; flex-direction: column; padding: 30px 20px; }
        .logo { font-size: 24px; font-weight: 700; color: white; display: flex; align-items: center; gap: 10px; margin-bottom: 40px; }
        .logo ion-icon { color: var(--primary); }
        .nav-link { display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: #ccc; text-decoration: none; border-radius: 8px; margin-bottom: 5px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: var(--primary); color: white; }
        .logout { margin-top: auto; color: #ff6b6b; background: rgba(255,107,107,0.1); }
        .logout:hover { background: #ff6b6b; color: white; }

        /* MAIN */
        .main { flex: 1; padding: 40px; overflow-y: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        /* BELL & BADGE */
        .notif-box { position: relative; font-size: 28px; color: #555; cursor: pointer; margin-right: 20px; }
        .badge { position: absolute; top: -5px; right: -5px; background: #e74c3c; color: white; font-size: 11px; font-weight: bold; padding: 3px 6px; border-radius: 50%; display: <?php echo $new_count > 0 ? 'block' : 'none'; ?>; }

        /* STATS CARDS */
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }
        .card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border-left: 5px solid var(--primary); }
        .card h3 { font-size: 32px; margin: 0; color: #333; }
        .card span { color: #777; font-size: 14px; text-transform: uppercase; }
        .icon-box { font-size: 40px; color: var(--primary); background: var(--bg); padding: 10px; border-radius: 50%; }

        /* FILTER BANNER */
        .filter-banner {
            background-color: #dff9fb; border: 1px solid #b3e4ec; color: #0c8599;
            padding: 15px; border-radius: 8px; margin-bottom: 20px;
            display: flex; justify-content: space-between; align-items: center;
        }

        /* TABLE */
        .table-box { background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        thead { background: #f1f3f5; }
        th { padding: 15px; font-size: 14px; color: #555; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #eee; color: #333; font-size: 15px; }
        tr:hover { background: #fdf2e9; cursor: pointer; }
        
        /* STATUS BADGES */
        .status { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status.new { background: #ffeaa7; color: #d35400; }
        .status.read { background: #dff9fb; color: #2980b9; }
        .status.completed { background: #c3fae8; color: #0ca678; } 
        
        .payment.pending { color: #e74c3c; font-weight: bold; }
        .payment.paid { color: #2ecc71; font-weight: bold; }

        /* ACTION BUTTONS */
        .btn-action { text-decoration: none; padding: 8px 12px; border-radius: 5px; font-size: 14px; margin-right: 5px; display: inline-block; transition:0.2s; }
        .btn-action:hover { opacity: 0.8; }
        .btn-del { background: #ffe3e3; color: #e03131; }
        .btn-approve { background: #d3f9d8; color: #2b8a3e; }
        .btn-complete { background: #e3fafc; color: #0c8599; }
        
        /* EMAIL LINK */
        .email-link { color: #2980b9; text-decoration: none; font-weight: 500; display: flex; align-items: center; gap: 5px; }
        .email-link:hover { text-decoration: underline; color: #e67e22; }

        /* MODAL */
        .modal-overlay { position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.6); display: none; justify-content: center; align-items: center; z-index: 1000; }
        .modal { background: white; padding: 30px; border-radius: 10px; width: 400px; max-width: 90%; position: relative; }
        .close-btn { position: absolute; top: 15px; right: 20px; font-size: 24px; cursor: pointer; color: #777; }
        .modal h2 { margin-top: 0; color: var(--primary); }
        .detail-row { margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 8px; }
        .detail-label { font-weight: bold; color: #555; font-size: 12px; text-transform: uppercase; }
        .detail-value { font-size: 16px; color: #333; margin-top: 4px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="logo">
            <ion-icon name="restaurant"></ion-icon> Omnifood
        </div>
        <a href="admin.php" class="nav-link active"><ion-icon name="grid-outline"></ion-icon> Dashboard</a>
        <a href="admin.php?export=true" class="nav-link"><ion-icon name="download-outline"></ion-icon> Export Data</a>
        <a href="index.php" class="nav-link" target="_blank"><ion-icon name="globe-outline"></ion-icon> View Website</a>
        
        <a href="admin.php?logout=true" class="logout nav-link"><ion-icon name="log-out-outline"></ion-icon> Log Out</a>
    </div>

    <div class="main">
        <div class="header">
            <h2 style="color: #333; margin: 0;">Welcome, <?php echo htmlspecialchars($admin_name); ?>!</h2>

            <div class="notif-box" onclick="window.location.href='admin.php?mark_read_all=true'">
                <ion-icon name="notifications-outline"></ion-icon>
                <span class="badge"><?php echo $new_count; ?></span>
            </div>
        </div>

        <div class="stats">
            <div class="card">
                <div class="icon-box"><ion-icon name="people-outline"></ion-icon></div>
                <div><h3><?php echo $total_count; ?></h3><span>Total Orders</span></div>
            </div>
            <div class="card">
                <div class="icon-box"><ion-icon name="wallet-outline"></ion-icon></div>
                <div><h3><?php echo $pending_count; ?></h3><span>Pending Pay</span></div>
            </div>
            <div class="card">
                <div class="icon-box"><ion-icon name="mail-unread-outline"></ion-icon></div>
                <div><h3><?php echo $new_count; ?></h3><span>New Inquiries</span></div>
            </div>
        </div>

        <?php if (!empty($filter_email)): ?>
        <div class="filter-banner">
            <span>
                <ion-icon name="funnel-outline" style="vertical-align: text-bottom;"></ion-icon> 
                Showing orders for: <strong><?php echo htmlspecialchars($filter_email); ?></strong>
            </span>
            <a href="admin.php" style="color: #c0392b; font-weight: bold; text-decoration: none;">× Clear Filter</a>
        </div>
        <?php endif; ?>

        <div class="table-box">
            <div style="padding: 20px; border-bottom: 1px solid #eee;">
                <h3 style="margin:0; color: #333;">Recent Orders & Inquiries</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th> <th>Email (History)</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_list && $result_list->num_rows > 0):
                        while($row = $result_list->fetch_assoc()):
                            $userName = !empty($row['Name']) ? $row['Name'] : "Guest";
                            // Get Phone from DB
                            $userPhone = !empty($row['phone_number']) ? $row['phone_number'] : "-";
                            
                            $statusClass = 'read';
                            if ($row['status'] == 'new') $statusClass = 'new';
                            if ($row['status'] == 'completed') $statusClass = 'completed';

                            $payClass = ($row['payment_status'] == 'paid') ? 'paid' : 'pending';
                            
                            // Maintain filter param
                            $filterStr = !empty($filter_email) ? "&email_filter=" . urlencode($filter_email) : "";
                    ?>
                    <tr onclick='showModal(<?php echo json_encode($row); ?>)'>
                        <td>#<?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($userName); ?></td>
                        <td><?php echo htmlspecialchars($userPhone); ?></td> <td onclick="event.stopPropagation()">
                            <a href="admin.php?email_filter=<?php echo urlencode($row['Email']); ?>" class="email-link" title="See all orders from this email">
                                <?php echo htmlspecialchars($row['Email']); ?>
                                <ion-icon name="search-circle-outline" style="font-size:16px;"></ion-icon>
                            </a>
                        </td>
                        
                        <td><span class="status <?php echo $statusClass; ?>"><?php echo strtoupper($row['status']); ?></span></td>
                        <td><span class="payment <?php echo $payClass; ?>"><?php echo strtoupper($row['payment_status']); ?></span></td>
                        <td onclick="event.stopPropagation()">
                            
                            <a href="admin.php?delete=<?php echo $row['id'] . $filterStr; ?>" class="btn-action btn-del" onclick="return confirm('Are you sure?')" title="Delete"><ion-icon name="trash-outline"></ion-icon></a>
                            
                            <?php if($row['payment_status'] == 'pending'): ?>
                            <a href="admin.php?approve_payment=<?php echo $row['id'] . $filterStr; ?>" class="btn-action btn-approve" title="Approve Payment"><ion-icon name="checkmark-circle-outline"></ion-icon></a>
                            <?php endif; ?>

                            <?php if($row['status'] != 'completed'): ?>
                            <a href="admin.php?complete_order=<?php echo $row['id'] . $filterStr; ?>" class="btn-action btn-complete" title="Mark Order Completed"><ion-icon name="bicycle-outline"></ion-icon></a>
                            <?php endif; ?>

                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="7" style="text-align:center; padding:20px;">No records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="detailModal" class="modal-overlay">
        <div class="modal">
            <span class="close-btn" onclick="closeModal()">×</span>
            <h2>Order Details</h2>
            
            <div class="detail-row">
                <div class="detail-label">Name</div>
                <div class="detail-value" id="m-name"></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Phone</div>
                <div class="detail-value" id="m-phone" style="font-weight:bold; color:#e67e22;"></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Email</div>
                <div class="detail-value" id="m-email"></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Date</div>
                <div class="detail-value" id="m-date"></div>
            </div>
            
            <div class="detail-row" style="border:none;">
                <div class="detail-label">Order Items / Message</div>
                <div class="detail-value" id="m-msg" style="background:#f9f9f9; padding:10px; border-radius:5px; height:100px; overflow-y:auto;"></div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <a href="#" id="m-approve-btn" class="btn-action btn-approve" style="flex:1; justify-content:center; padding:15px; display:none; text-align:center;">
                    <ion-icon name="checkmark-circle"></ion-icon> Approve Pay
                </a>
                <a href="#" id="m-complete-btn" class="btn-action btn-complete" style="flex:1; justify-content:center; padding:15px; display:none; text-align:center;">
                    <ion-icon name="bicycle"></ion-icon> Complete Order
                </a>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('detailModal');
        const mName = document.getElementById('m-name');
        const mPhone = document.getElementById('m-phone'); // Element for Phone
        const mEmail = document.getElementById('m-email');
        const mDate = document.getElementById('m-date');
        const mMsg = document.getElementById('m-msg');
        
        const mApproveBtn = document.getElementById('m-approve-btn');
        const mCompleteBtn = document.getElementById('m-complete-btn');

        function showModal(data) {
            mName.innerText = data.Name || 'Guest';
            mPhone.innerText = data.phone_number || 'N/A'; // Fill Phone Number
            mEmail.innerText = data.Email;
            mDate.innerText = data.created_at;

            // Generate Map Link if Location exists
            let orderText = data.order_items;
            let locationLink = "";
            
            // Check for [Loc: 27.xxx, 85.xxx] pattern
            if (orderText && orderText.includes("[Loc:")) {
                let parts = orderText.split("[Loc: ");
                if (parts.length > 1) {
                    let coords = parts[1].split("]")[0].trim();
                    locationLink = `<br><br><a href="https://www.google.com/maps?q=${coords}" target="_blank" 
                    style="display:inline-block; padding:8px 12px; background:#4285F4; color:white; text-decoration:none; border-radius:5px; font-size:12px;">
                    <ion-icon name="map-outline"></ion-icon> View Location on Map
                    </a>`;
                }
            }
            mMsg.innerHTML = orderText + locationLink;

            // Button Logic
            const filterParam = "<?php echo !empty($filter_email) ? '&email_filter='.urlencode($filter_email) : ''; ?>";

            if (data.payment_status === 'pending') {
                mApproveBtn.style.display = 'block';
                mApproveBtn.href = "admin.php?approve_payment=" + data.id + filterParam;
            } else {
                mApproveBtn.style.display = 'none';
            }

            if (data.status !== 'completed') {
                mCompleteBtn.style.display = 'block';
                mCompleteBtn.href = "admin.php?complete_order=" + data.id + filterParam;
            } else {
                mCompleteBtn.style.display = 'none';
            }
            
            modal.style.display = 'flex';
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>