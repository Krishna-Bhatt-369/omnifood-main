<?php
session_start();

// --- 1. SECURITY LOCK ---
/*
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
*/

// --- 2. LOGOUT LOGIC ---
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

include 'connect.php';

// --- 3. DELETE LOGIC ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM cafe WHERE id = $id");
    header("Location: admin.php?status=deleted");
    exit;
}

// --- 4. EXPORT LOGIC ---
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="omnifood_users.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Name', 'Email', 'Source'));
    
    $query = "SELECT * FROM cafe ORDER BY id DESC";
    $rows = $conn->query($query);
    while ($row = $rows->fetch_assoc()) {
        // IMPORT CSV
        $name = !empty($row['name']) ? $row['name'] : (!empty($row['full_name']) ? $row['full_name'] : "Unknown");
        $email = !empty($row['email']) ? $row['email'] : "No Email";
        $source = !empty($row['source']) ? $row['source'] : (!empty($row['select_where']) ? $row['select_where'] : "Unknown");
        fputcsv($output, array($row['id'], $name, $email, $source));
    }
    fclose($output);
    exit;
}

// --- 5. FETCH DATA ---
$sql = "SELECT * FROM cafe ORDER BY id DESC";
$result = $conn->query($sql);
$total_users = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Omnifood Admin Dashboard</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <style>
        :root {
            --primary: #e67e22;
            --primary-dark: #cf711f;
            --bg: #f9f9f9;
            --sidebar-bg: #2e2e2e;
            --text-dark: #333;
            --text-light: #888;
        }

        body {
            font-family: 'Rubik', sans-serif;
            margin: 0;
            display: flex;
            background-color: var(--bg);
            color: #444;
            min-height: 100vh;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            color: #fff;
            display: flex;
            flex-direction: column;
            padding: 30px 20px;
            position: fixed;
            height: 100vh;
            box-sizing: border-box;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logo ion-icon { color: var(--primary); }

        .nav-link {
            text-decoration: none;
            color: #bbb;
            font-size: 18px;
            padding: 12px 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: 0.3s;
            margin-bottom: 8px;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: var(--primary);
            color: #fff;
        }

        .nav-link.logout {
            margin-top: auto;
            background: rgba(255, 80, 80, 0.1);
            color: #ff6b6b;
        }
        .nav-link.logout:hover { background: #fa5252; color: white; }

        /* --- MAIN CONTENT --- */
        .main-content {
            margin-left: 260px;
            padding: 40px 60px;
            width: 100%;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 32px;
            color: var(--text-dark);
            margin: 0;
        }

        .btn-export {
            background-color: #27ae60;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 9px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(39, 174, 96, 0.3);
        }
        .btn-export:hover { background-color: #219150; transform: translateY(-2px); }

        /* --- STATS CARD --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: #fff;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            border-left: 5px solid var(--primary);
        }

        .stat-icon {
            font-size: 48px;
            color: var(--primary);
            background: #fdf2e9;
            padding: 10px;
            border-radius: 50%;
        }

        .stat-info h3 { margin: 0; font-size: 36px; color: #333; }
        .stat-info span { color: #888; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }

        /* --- TABLE --- */
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        table { width: 100%; border-collapse: collapse; text-align: left; }
        
        thead { background-color: #f1f3f5; }
        
        th {
            padding: 20px;
            font-weight: 600;
            color: #555;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }

        td {
            padding: 18px 20px;
            border-bottom: 1px solid #eee;
            color: #444;
        }

        tr:hover { background-color: #fafafa; }

        .tag {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 600;
            background: #e9ecef;
            color: #495057;
        }

        .btn-delete {
            background: #ffe3e3;
            color: #e03131;
            border: none;
            padding: 8px;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.2s;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            width: 32px;
            height: 32px;
        }
        .btn-delete:hover { background: #e03131; color: white; }

    </style>
</head>
<body>

    <div class="sidebar">
        <div class="logo"><ion-icon name="restaurant-outline"></ion-icon> Omnifood</div>
        
        <a href="#" class="nav-link active">
            <ion-icon name="grid-outline"></ion-icon> Dashboard
        </a>
        <a href="admin.php?export=true" class="nav-link">
            <ion-icon name="download-outline"></ion-icon> Export CSV
        </a>
        <a href="index.php" class="nav-link">
            <ion-icon name="globe-outline"></ion-icon> View Website
        </a>

        <a href="admin.php?logout=true" class="nav-link logout">
            <ion-icon name="log-out-outline"></ion-icon> Log Out
        </a>
    </div>

    <div class="main-content">
        
        <div class="header">
            <div>
                <h1>Dashboard</h1>
                <p style="color: #888; margin-top: 5px;">Welcome back, Krishna!</p>
            </div>
            <a href="admin.php?export=true" class="btn-export">
                <ion-icon name="cloud-download-outline"></ion-icon> Download Data
            </a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <ion-icon name="people-outline"></ion-icon>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_users; ?></h3>
                    <span>Total Signups</span>
                </div>
            </div>
        </div>

        <h3 style="margin-bottom: 20px; color: #555;">Recent Signups</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email Address</th>
                        <th>Source</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <?php 
                            
                            $name = "Unknown";
                            if(isset($row['Name'])) $name = $row['Name'];
                            elseif(isset($row['Name'])) $name = $row['Name'];
                            
                            // Check for email
                            $email = "No Email";
                            if(isset($row['Email'])) $email = $row['Email'];
                            
                            // Check for source
                            $source = "Unknown";
                            if(isset($row['Source'])) $source = $row['Source'];
                            elseif(isset($row['select_where'])) $source = $row['select_where'];
                        ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($name); ?></strong></td>
                            <td><?php echo htmlspecialchars($email); ?></td>
                            <td><span class="tag"><?php echo htmlspecialchars($source); ?></span></td>
                            <td style="display: flex; justify-content: flex-end;">
                                <a href="admin.php?delete=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this user?');">
                                    <ion-icon name="trash-outline"></ion-icon>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: #888;">
                                No signups found yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>