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
    header("Location: admin.php?msg=deleted");
    exit;
}

// APPROVE PAYMENT
if (isset($_GET['approve_payment'])) {
    $id = intval($_GET['approve_payment']);
    // Set payment to PAID and mark notification as READ
    $conn->query("UPDATE cafe SET payment_status = 'paid', status = 'read' WHERE id = $id");
    header("Location: admin.php?msg=approved");
    exit;
}

// MARK AS READ
if (isset($_GET['mark_read'])) {
    $id = intval($_GET['mark_read']);
    $conn->query("UPDATE cafe SET status = 'read' WHERE id = $id");
    header("Location: admin.php");
    exit;
}

// --- 3. EXPORT CSV ---
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="omnifood_data.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Name', 'Email', 'Order Items', 'Payment Status', 'Date'));
    
    $rows = $conn->query("SELECT * FROM cafe ORDER BY id DESC");
    while ($row = $rows->fetch_assoc()) {
        $name = !empty($row['Name']) ? $row['Name'] : "Guest"; 
        $pay_stat = isset($row['payment_status']) ? $row['payment_status'] : 'none';
        fputcsv($output, array($row['id'], $name, $row['Email'], $row['order_items'], $pay_stat, $row['created_at']));
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

        /* TABLE */
        .table-box { background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        thead { background: #f1f3f5; }
        th { padding: 15px 20px; font-size: 13px; text-transform: uppercase; color: #555; }
        td { padding: 15px 20px; border-bottom: 1px solid #eee; color: #444; vertical-align: middle; }
        
        /* STATUS TAGS */
        .tag { padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .tag-pending { background: #fff3cd; color: #856404; }
        .tag-paid { background: #d4edda; color: #155724; }
        .tag-none { background: #eee; color: #777; }
        
        .row-new { background: #fff9f2; border-left: 4px solid var(--primary); }

        /* BUTTONS */
        .btn-action { text-decoration: none; padding: 8px 12px; border-radius: 5px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; border: none; cursor: pointer; transition: 0.2s; }
        .btn-approve { background: #27ae60; color: white; }
        .btn-approve:hover { background: #219150; }
        .btn-view { background: #333; color: white; }
        .btn-view:hover { background: #555; }
        .btn-del { color: #e74c3c; background: none; font-size: 20px; padding: 5px; }
        .btn-del:hover { background: #ffe3e3; border-radius: 5px; }

        /* MODAL */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000; }
        .modal-content { background: white; width: 500px; padding: 30px; border-radius: 12px; animation: slide 0.3s; position: relative; }
        @keyframes slide { from{transform:translateY(-20px);opacity:0} to{transform:translateY(0);opacity:1} }
        .data-row { margin-bottom: 15px; }
        .label { font-size: 12px; color: #888; text-transform: uppercase; display: block; margin-bottom: 5px; }
        .val { font-size: 16px; color: #333; font-weight: 500; }
        .msg-box { background: #fdf2e9; padding: 15px; border-radius: 8px; border: 1px solid #e67e22; margin-top: 5px; }
        .close { position: absolute; top: 20px; right: 20px; font-size: 24px; cursor: pointer; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="logo"><ion-icon name="restaurant"></ion-icon> Omnifood</div>
        <a href="#" class="nav-link active"><ion-icon name="grid"></ion-icon> Dashboard</a>
        <a href="index.php" class="nav-link" target="_blank"><ion-icon name="globe"></ion-icon> Visit Site</a>
        <a href="admin.php?export=true" class="nav-link"><ion-icon name="download"></ion-icon> Export CSV</a>
        <a href="admin.php?logout=true" class="nav-link logout"><ion-icon name="log-out"></ion-icon> Logout</a>
    </div>

    <div class="main">
        <div class="header">
            <div>
                <h1 style="margin:0;">Admin Dashboard</h1>
                <p style="margin:5px 0 0; color:#777;">Manage orders, approvals, and inquiries.</p>
            </div>
            <div class="notif-box">
                <ion-icon name="notifications"></ion-icon>
                <span class="badge"><?php echo $new_count; ?></span>
            </div>
        </div>

        <div class="stats">
            <div class="card">
                <div class="icon-box"><ion-icon name="cart"></ion-icon></div>
                <div><h3><?php echo $total_count; ?></h3><span>Total Orders</span></div>
            </div>
            <div class="card">
                <div class="icon-box" style="color:#e74c3c; background:#ffe3e3;"><ion-icon name="time"></ion-icon></div>
                <div><h3 style="color:#e74c3c;"><?php echo $pending_count; ?></h3><span>Pending Approvals</span></div>
            </div>
            <div class="card">
                <div class="icon-box" style="color:#27ae60; background:#d4edda;"><ion-icon name="checkmark-circle"></ion-icon></div>
                <div><h3><?php echo $total_count - $pending_count; ?></h3><span>Completed</span></div>
            </div>
        </div>

        <h3 style="color:#555;">Recent Orders</h3>
        <div class="table-box">
            <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Customer</th>
                        <th>Order Details</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // CONNECTING TO CAFE TABLE
                    $result = $conn->query("SELECT * FROM cafe ORDER BY id DESC");
                    if ($result->num_rows > 0):
                        while($row = $result->fetch_assoc()):
                            $name = !empty($row['Name']) ? $row['Name'] : "Guest";
                            $pay_status = isset($row['payment_status']) ? $row['payment_status'] : 'none';
                            $status = isset($row['status']) ? $row['status'] : 'read';
                            $rowClass = ($status == 'new') ? 'row-new' : '';
                    ?>
                    <tr class="<?php echo $rowClass; ?>">
                        <td>
                            <?php if($status == 'new'): ?>
                                <span class="tag" style="background:#e67e22; color:white;">NEW</span>
                            <?php else: ?>
                                <span class="tag" style="background:#eee; color:#888;">READ</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="val"><?php echo htmlspecialchars($name); ?></div>
                            <div style="font-size:12px; color:#888;"><?php echo htmlspecialchars($row['Email']); ?></div>
                        </td>
                        <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <?php echo htmlspecialchars($row['order_items']); ?>
                        </td>
                        <td>
                            <?php if($pay_status == 'pending'): ?>
                                <span class="tag tag-pending">Pending</span>
                            <?php elseif($pay_status == 'paid'): ?>
                                <span class="tag tag-paid">PAID</span>
                            <?php else: ?>
                                <span class="tag tag-none">-</span>
                            <?php endif; ?>
                        </td>
                        <td style="display: flex; align-items: center; gap: 10px;">
                            
                            <?php if($pay_status == 'pending'): ?>
                                <a href="admin.php?approve_payment=<?php echo $row['id']; ?>" class="btn-action btn-approve" onclick="return confirm('Confirm payment received for <?php echo $name; ?>?');">
                                    <ion-icon name="checkmark"></ion-icon> Approve
                                </a>
                            <?php endif; ?>

                            <button class="btn-action btn-view" onclick='showModal(<?php echo json_encode($row); ?>)'>
                                <ion-icon name="eye"></ion-icon> View
                            </button>

                            <a href="admin.php?delete=<?php echo $row['id']; ?>" class="btn-del" onclick="return confirm('Delete this record?');">
                                <ion-icon name="trash"></ion-icon>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="5" style="text-align:center; padding:30px;">No records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 style="margin-top:0;">Order Details</h2>
            
            <div class="data-row">
                <span class="label">Customer Name</span>
                <div class="val" id="m-name"></div>
            </div>
            <div class="data-row">
                <span class="label">Email Address</span>
                <div class="val" id="m-email"></div>
            </div>
            <div class="data-row">
                <span class="label">Date</span>
                <div class="val" id="m-date"></div>
            </div>
            <div class="data-row">
                <span class="label">Order / Message</span>
                <div class="val msg-box" id="m-msg"></div>
            </div>

            <a href="#" id="m-approve-btn" class="btn-action btn-approve" style="width:100%; justify-content:center; padding:15px; margin-top:20px; display:none;">
                <ion-icon name="checkmark-circle"></ion-icon> Approve Payment
            </a>
        </div>
    </div>

    <script>
        const modal = document.getElementById('detailModal');
        const mName = document.getElementById('m-name');
        const mEmail = document.getElementById('m-email');
        const mDate = document.getElementById('m-date');
        const mMsg = document.getElementById('m-msg');
        const mApproveBtn = document.getElementById('m-approve-btn');

        function showModal(data) {
            mName.innerText = data.Name || 'Guest';
            mEmail.innerText = data.Email;
            mDate.innerText = data.created_at;
            mMsg.innerText = data.order_items;

            // Show Approve button inside modal only if pending
            if (data.payment_status === 'pending') {
                mApproveBtn.style.display = 'flex';
                mApproveBtn.href = "admin.php?approve_payment=" + data.id;
            } else {
                mApproveBtn.style.display = 'none';
            }
            
            modal.style.display = 'flex';
        }

        function closeModal() { modal.style.display = 'none'; }
        window.onclick = function(e) { if(e.target == modal) closeModal(); }
    </script>
</body>
</html>