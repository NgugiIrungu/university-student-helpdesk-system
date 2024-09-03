<?php
session_start();

// Database connection details
$servername = "localhost";
$username = "Project4th";
$password = "Assasination26";
$dbname = "customerdb";

// Create a new connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['customer_care', 'hod', 'dean', 'deputy_vice_chancellor', 'vice_chancellor'])) {
    die("Error: Unauthorized access. This page is only for authorized personnel.");
}

// Function to execute SQL query and return results
function executeQuery($conn, $sql) {
    $result = $conn->query($sql);
    if ($result === false) {
        die("Error executing query: " . $conn->error);
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Define reports
$reports = [
    1 => ["name" => "Issues by Status", "query" => "SELECT status, COUNT(*) as count FROM issue GROUP BY status"],
    2 => ["name" => "User Activity by Role", "query" => "SELECT role, COUNT(*) as user_count FROM user GROUP BY role"],
    3 => ["name" => "Programs by Department", "query" => "SELECT d.department_name, COUNT(p.program_id) as program_count FROM department d LEFT JOIN program p ON d.department_id = p.department_id GROUP BY d.department_id"],
    4 => ["name" => "List all issues along with their responses", "query" => "SELECT i.issue_id, i.issue_description, r.response_text, r.response_date FROM issue i LEFT JOIN response r ON i.issue_id = r.issue_id ORDER BY i.issue_id, r.response_date"],
    5 => ["name" => "Students with Gmail email addresses", "query" => "SELECT name, email FROM student WHERE email LIKE '%@gmail.com'"],
    6 => ["name" => "Most recent response for each issue", "query" => "SELECT r1.issue_id, r1.response_text, r1.response_date FROM response r1 INNER JOIN (SELECT issue_id, MAX(response_date) AS latest_response FROM response GROUP BY issue_id) r2 ON r1.issue_id = r2.issue_id AND r1.response_date = r2.latest_response"]
];

// Generate report if one is selected
$selected_report = isset($_GET['report']) ? intval($_GET['report']) : null;
$report_data = null;
if ($selected_report && isset($reports[$selected_report])) {
    $report_data = executeQuery($conn, $reports[$selected_report]['query']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        header {
            background-color: #f0a608;
            color: #fff;
            padding: 1rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #000;
            font-weight: bold;
        }

        .navbar-logo {
            height: 40px;
            margin-right: 10px;
        }

        .navbar-links {
            list-style: none;
            display: flex;
        }

        .nav-item {
            margin-left: 20px;
        }

        .nav-link {
            color: #fff;
            text-decoration: none;
        }

        .container {
            width: 80%;
            margin: 100px auto 0;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1, h2 {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .report-list {
            list-style-type: none;
            padding: 0;
        }

        .report-list li {
            margin-bottom: 10px;
        }

        .report-list a {
            text-decoration: none;
            color: #f0a608;
        }

        .report-list a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a class="navbar-brand" href="dashboard.php">
                <img src="logo.png" alt="Logo" class="navbar-logo">
                Catholic University of Eastern Africa
            </a>
            <ul class="navbar-links">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="landing.html">Logout</a>
                </li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Reports</h1>
        
        <h2>Available Reports</h2>
        <ul class="report-list">
            <?php foreach ($reports as $id => $report): ?>
                <li><a href="?report=<?php echo $id; ?>"><?php echo htmlspecialchars($report['name']); ?></a></li>
            <?php endforeach; ?>
        </ul>

        <?php if ($selected_report): ?>
            <h2><?php echo htmlspecialchars($reports[$selected_report]['name']); ?></h2>
            <?php if ($report_data): ?>
                <table>
                    <thead>
                        <tr>
                            <?php foreach (array_keys($report_data[0]) as $column): ?>
                                <th><?php echo htmlspecialchars($column); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report_data as $row): ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                    <td><?php echo htmlspecialchars($value); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No data available for this report.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>