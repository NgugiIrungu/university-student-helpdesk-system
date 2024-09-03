<?php
// Start the session to store and retrieve data for the user
session_start();

// Debugging: Log the current session contents to the error log
error_log("Session contents: " . print_r($_SESSION, true));

// Database connection details
$servername = "localhost";
$username = "Project4th";
$password = "Assasination26";
$dbname = "customerdb";

// Create a new MySQLi connection instance using the provided details
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    // If there is a connection error, terminate the script and display an error message
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in by verifying if 'user_id' is set in the session
if (!isset($_SESSION['user_id'])) {
    // If not, terminate the script and prompt the user to log in
    die("Error: User not logged in. Please <a href='login.php'>login</a> first.");
}

// Retrieve the user ID from the session
$user_id = $_SESSION['user_id'];

// Fetch the user's role from the database
$sql = "SELECT role FROM user WHERE user_id = $user_id";
$result = $conn->query($sql);

// Check if the user's role was found
if ($row = $result->fetch_assoc()) {
    $role = $row['role'];
    // Store the user's role in the session for future use
    $_SESSION['role'] = $role;
} else {
    // If the user is not found in the database, terminate the script with an error message
    die("Error: User not found in database.");
}

// Check if the user is a lecturer
if ($role !== 'lecturer') {
    // If not, terminate the script with an error message
    die("Error: Unauthorized access. This page is only for lecturers.");
}

// Handle the form submission for responding to an issue and updating its status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_response'])) {
    // Retrieve the issue ID and response text from the form submission
    $issue_id = $_POST['issue_id'];
    $response_text = $_POST['response_text'];
    $response_date = date('Y-m-d H:i:s'); // Get the current date and time

    // Insert the response into the database
    $sql = "INSERT INTO response (issue_id, response_text, response_date) VALUES ('$issue_id', '$response_text', '$response_date')";
    if ($conn->query($sql)) {
        // If successful, update the issue status to 'closed'
        $sql = "UPDATE issue SET status = 'closed' WHERE issue_id = '$issue_id'";
        if ($conn->query($sql)) {
            // Display a success message to the user
            echo "<script>alert('Response submitted successfully and issue closed.');</script>";
        } else {
            // If there was an error updating the issue status, display an error message
            echo "<script>alert('Error updating issue status: " . $conn->error . "');</script>";
        }
    } else {
        // If there was an error inserting the response, display an error message
        echo "<script>alert('Error submitting response: " . $conn->error . "');</script>";
    }

    // Redirect to the same page to refresh the content
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch issues assigned to the logged-in lecturer
$sql = "SELECT i.issue_id, i.issue_title, i.issue_description, i.status, s.first_name, s.last_name 
        FROM issue i 
        JOIN student s ON i.student_id = s.student_id 
        WHERE i.forwarded_to = 'lecturer' AND i.status != 'closed'
        ORDER BY i.issue_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard</title>
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
            <a class="navbar-brand" href="lecturer_dashboard.php">
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
        <h1>Lecturer Dashboard</h1>
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