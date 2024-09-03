<?php
session_start();

// Debugging: Let's see what's in the session
error_log("Session contents: " . print_r($_SESSION, true));

// Database connection details
$servername = "localhost";
$username = "Project4th";
$password = "Assasination26";
$dbname = "customerdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in. Please <a href='login.php'>login</a> first.");
}

// Fetch user role from database
$user_id = $_SESSION['user_id'];
$sql = "SELECT role FROM user WHERE user_id = '$user_id'";
$result = $conn->query($sql);

if ($row = $result->fetch_assoc()) {
    $role = $row['role'];
    $_SESSION['role'] = $role; // Store role in session for future use
} else {
    die("Error: User not found in database.");
}

// Check if user is a dean
if ($role !== 'dean') {
    die("Error: Unauthorized access. This page is only for deans.");
}

// Handle issue response and status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_response'])) {
    $issue_id = $_POST['issue_id'];
    $response_text = $_POST['response_text'];
    $response_date = date('Y-m-d H:i:s');

    // Insert response
    $sql = "INSERT INTO response (issue_id, response_text, response_date) VALUES ('$issue_id', '$response_text', '$response_date')";
    if ($conn->query($sql)) {
        // Update issue status to 'closed'
        $sql = "UPDATE issue SET status = 'closed' WHERE issue_id = '$issue_id'";
        $conn->query($sql);
        echo "<script>alert('Response submitted successfully and issue closed.');</script>";
    } else {
        echo "<script>alert('Error submitting response: " . $conn->error . "');</script>";
    }

    // Redirect to refresh the page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch issues forwarded to the dean
$sql = "SELECT i.issue_id, i.issue_title, i.issue_description, i.status, s.first_name, s.last_name 
        FROM issue i 
        JOIN student s ON i.student_id = s.student_id 
        WHERE i.forwarded_to = 'dean' AND i.status != 'closed'
        ORDER BY i.issue_id DESC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dean Dashboard</title>
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

        .closed {
            background-color: #c3e6cb;
            color: #155724;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
        }

        #responseText {
            width: 100%;
            height: 100px;
            margin-bottom: 10px;
        }

        .respond-button {
            padding: 5px 10px;
            background-color: #007bff;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .respond-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a class="navbar-brand" href="Dean_dashboard.php">
                <img src="logo.png" alt="Logo" class="navbar-logo">
                Catholic University of Eastern Africa
            </a>
            <ul class="navbar-links">
                <li class="nav-item">
                    <a class="nav-link" href="landing.html">Logout</a>
                </li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Dean Dashboard</h1>
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Student</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo ($row['issue_id']); ?></td>
                            <td><?php echo ($row['issue_title']); ?></td>
                            <td><?php echo ($row['issue_description']); ?></td>
                            <td><?php echo ($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td><span class="status <?php echo $row['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?></span></td>
                            <td>
                                <button class="respond-button" onclick="openResponseModal(<?php echo $row['issue_id']; ?>)">Respond</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No issues found.</p>
        <?php endif; ?>
    </div>

    <!-- Response Modal -->
    <div id="responseModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeResponseModal()">&times;</span>
            <h2>Respond to Issue</h2>
            <form id="responseForm" method="POST">
                <input type="hidden" name="issue_id" id="modalIssueId">
                <textarea name="response_text" id="responseText" required></textarea>
                <button type="submit" name="submit_response" class="respond-button">Submit Response</button>
            </form>
        </div>
    </div>

    <script>
        var modal = document.getElementById("responseModal");

        function openResponseModal(issueId) {
            modal.style.display = "block";
            document.getElementById("modalIssueId").value = issueId;
        }

        function closeResponseModal() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                closeResponseModal();
            }
        }
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>