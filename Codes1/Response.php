<?php
session_start();

// Database connection details
$servername = "localhost";
$username = "Project4th";
$password = "Assasination26";
$dbname = "customerdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in.");
}

$student_id = $_SESSION['user_id'];

// Fetch issues and responses for the logged-in user
$sql = "
    SELECT i.issue_id, i.issue_title, i.issue_description, i.status, 
           r.response_text, r.response_date
    FROM issue i
    LEFT JOIN response r ON i.issue_id = r.issue_id
    WHERE i.student_id = $student_id
    ORDER BY i.issue_id DESC
";
$result = $conn->query($sql);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Issues and Responses</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        header {
            background-color: #f0a608;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            z-index: 1000;
            top:0;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #000000;
        }

        .navbar-logo {
            margin-right: 10px;
        }

        .navbar-links {
            list-style-type: none;
            display: flex;
            margin: 0;
            padding: 0;
        }

        .nav-item {
            margin-right: 15px;
        }

        .nav-link {
            text-decoration: none;
            color: #ffffff;
            font-weight: bold;
            cursor: pointer;
        }

        .container {
            margin-top: 80px;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1 {
            color: #333333;
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

        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
        }

        .open {
            background-color: #ffeeba;
            color: #856404;
        }

        .in_progress {
            background-color: #b8daff;
            color: #004085;
        }

        .resolved {
            background-color: #c3e6cb;
            color: #155724;
        }

        .response {
            margin-top: 10px;
            padding: 10px;
            background-color: #f9f9f9;
            border-left: 3px solid #007bff;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a class="navbar-brand" href="Dashboard.html">
                <img src="logo.png" alt="Logo" class="navbar-logo">
                Catholic University of Eastern Africa
            </a>
            <ul class="navbar-links">
                <li class="nav-item">
                    <a class="nav-link" href="Dashboard.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="submit_issue.php">Submit Issue</a>
                </li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>My Issues and Responses</h1>
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Response</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['issue_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['issue_title']); ?></td>
                            <td><?php echo htmlspecialchars($row['issue_description']); ?></td>
                            <td><span class="status <?php echo $row['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?></span></td>
                            <td>
                                <?php if ($row['response_text']): ?>
                                    <div class="response">
                                        <?php echo htmlspecialchars($row['response_text']); ?>
                                        <br>
                                        <small>Responded on: <?php echo date('F j, Y, g:i a', strtotime($row['response_date'])); ?></small>
                                    </div>
                                <?php else: ?>
                                    <div class="response">No response yet. The issue is in progress.</div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No issues found.</p>
        <?php endif; ?>
    </div>
</body>
</html>