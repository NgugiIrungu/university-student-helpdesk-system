<?php
session_start(); // Start a new session or resume the existing session

// Debugging: Log the contents of the session
// error_log("Session contents: " . print_r($_SESSION, true));

// Database connection details
$servername = "localhost"; // Database server name
$username = "Project4th"; // Database username
$password = "Assasination26"; // Database password
$dbname = "customerdb"; // Database name

// Create a new connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // Stop execution if the connection failed
}

// Check if user is logged in by verifying session variable
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in. Please <a href='login.php'>login</a> first."); // Stop execution if the user is not logged in
}

// Fetch user role from the database
$user_id = $_SESSION['user_id']; // Get the user ID from the session
$sql = "SELECT role FROM user WHERE user_id = $user_id"; // SQL statement to fetch user role
$result = $conn->query($sql);

// Check if a row was fetched
if ($row = $result->fetch_assoc()) {
    $role = $row['role']; // Store the role in a variable
    $_SESSION['role'] = $role; // Store the role in the session for future use
} else {
    die("Error: User not found in database."); // Stop execution if no user was found
}

// Check if the user is a customer care representative
if ($role !== 'customer_care') {
    die("Error: Unauthorized access. This page is only for customer care representatives."); // Stop execution if the user is not authorized
}

// Handle issue forwarding and status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Check if the request method is POST
    if (isset($_POST['forward'])) { // Check if the forward button was pressed
        $issue_id = $_POST['issue_id']; // Get the issue ID from the form
        $forward_to = $_POST['forward_to']; // Get the forward to value from the form
        
        // Validate forward_to value
        $allowed_forward_to = ['lecturer', 'hod', 'dean']; // Allowed values for forwarding
        if (in_array($forward_to, $allowed_forward_to)) {
            // SQL statement to update issue status and forwarded to field
            $sql = "UPDATE issue SET status = 'in progress', forwarded_to = '$forward_to' WHERE issue_id = $issue_id";
            error_log("Forwarding issue $issue_id to: $forward_to"); // Log forwarding action
            if ($conn->query($sql)) {
                error_log("Forward successful"); // Log success message
            } else {
                error_log("Forward failed: " . $conn->error); // Log error message
            }
        } else {
            error_log("Invalid forward_to value: $forward_to"); // Log invalid value
            echo "Invalid forward_to value"; // Display error message
        }
    } elseif (isset($_POST['update_status'])) { // Check if the update status button was pressed
        $issue_id = $_POST['issue_id']; // Get the issue ID from the form
        $new_status = $_POST['new_status']; // Get the new status from the form
        
        // Validate new_status value
        $allowed_statuses = ['open', 'in progress', 'closed']; // Allowed status values
        if (in_array($new_status, $allowed_statuses)) {
            // SQL statement to update issue status
            $sql = "UPDATE issue SET status = '$new_status' WHERE issue_id = $issue_id";
            error_log("Updating status of issue $issue_id to: $new_status"); // Log status update action
            if ($conn->query($sql)) {
                error_log("Status update successful"); // Log success message
            } else {
                error_log("Status update failed: " . $conn->error); // Log error message
            }
        } else {
            error_log("Invalid status value: $new_status"); // Log invalid value
            echo "Invalid status value"; // Display error message
        }
    } elseif (isset($_POST['submit_response'])) { // Check if the submit response button was pressed
        $issue_id = $_POST['issue_id']; // Get the issue ID from the form
        $response_text = $_POST['response_text']; // Get the response text from the form
        $response_date = date('Y-m-d H:i:s'); // Get the current date and time

        // SQL statement to insert response
        $sql = "INSERT INTO response (issue_id, response_text, response_date) VALUES ($issue_id, '$response_text', '$response_date')";

        if ($conn->query($sql)) {
            // SQL statement to update issue status if it's not already closed
            $sql = "UPDATE issue SET status = 'in progress' WHERE issue_id = $issue_id AND status != 'closed'";
            $conn->query($sql);
            echo "<script>alert('Response submitted successfully.');</script>"; // Display success message
        } else {
            echo "<script>alert('Error submitting response: " . $conn->error . "');</script>"; // Display error message
        }
    }
    
    // Redirect to refresh the page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// SQL statement to fetch all issues, ordered by status
$sql = "SELECT i.issue_id, i.issue_title, i.issue_description, i.status, s.first_name, s.last_name 
        FROM issue i 
        JOIN student s ON i.student_id = s.student_id 
        ORDER BY i.status, i.issue_id DESC";
$result = $conn->query($sql);

// Group issues by status
$grouped_issues = []; // Initialize an array to group issues
while ($row = $result->fetch_assoc()) {
    $grouped_issues[$row['status']][] = $row; // Group issues by status
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> <!-- Set the character encoding for the document -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Set the viewport to ensure proper rendering on mobile devices -->
    <title>Customer Care Dashboard</title> <!-- Set the title of the page -->
    <style>
        /* Style for the body of the document */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        /* Style for the header of the document */
        header {
            background-color: #f0a608;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            z-index: 1000;
            top:0;
        }

        /* Style for the navigation bar */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
        }

        /* Style for the navigation brand */
        .navbar-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #000000;
        }

        /* Style for the navigation logo */
        .navbar-logo {
            margin-right: 10px;
        }

        /* Style for the navigation links */
        .navbar-links {
            list-style-type: none;
            display: flex;
            margin: 0;
            padding: 0;
        }

        /* Style for each navigation item */
        .nav-item {
            margin-right: 15px;
        }

        /* Style for each navigation link */
        .nav-link {
            text-decoration: none;
            color: #ffffff;
            font-weight: bold;
            cursor: pointer;
        }

        /* Style for the main container */
        .container {
            margin-top: 80px;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        /* Style for headings */
        h1 {
            color: #333333;
        }

        /* Style for tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        /* Style for table headers and cells */
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        /* Style for table headers */
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        /* Style for table rows on hover */
        tr:hover {
            background-color: #f5f5f5;
        }

        /* Style for issue status */
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
        }

        /* Style for open issues */
        .open {
            background-color: #ffeeba;
            color: #856404;
        }

        /* Style for issues in progress */
        .in_progress {
            background-color: #b8daff;
            color: #004085;
        }

        /* Style for closed issues */
        .closed {
            background-color: #c3e6cb;
            color: #155724;
        }

        /* Style for status groups */
        .status-group {
            margin-bottom: 30px;
        }
        
        /* Style for status titles */
        .status-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 10px;
            padding: 5px 10px;
            border-radius: 5px;
        }
        
        /* Style for open status titles */
        .status-title.open {
            background-color: #ffeeba;
            color: #856404;
        }
        
        /* Style for in-progress status titles */
        .status-title.in_progress {
            background-color: #b8daff;
            color: #004085;
        }
        
        /* Style for closed status titles */
        .status-title.closed {
            background-color: #c3e6cb;
            color: #155724;
        }

        /* Style for action forms */
        .action-form {
            display: flex;
            gap: 10px;
        }
        
        /* Style for action select and buttons */
        .action-select, .action-button {
            padding: 5px;
            border-radius: 4px;
        }
        
        /* Style for action buttons */
        .action-button {
            background-color: #007bff;
            color: #ffffff;
            border: none;
            cursor: pointer;
        }
        
        /* Style for action buttons on hover */
        .action-button:hover {
            background-color: #0056b3;
        }

        /* Style for modal */
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

        /* Style for modal content */
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
        }

        /* Style for close button */
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        /* Style for close button on hover */
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Style for response text area */
        #responseText {
            width: 100%;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a class="navbar-brand" href="customercare_dash.php">
                <img src="logo.png" alt="Logo" class="navbar-logo"> <!-- Logo for the navigation brand -->
                Catholic University of Eastern Africa <!-- Name of the institution -->
            </a>
            <ul class="navbar-links">
                <li class="nav-item">
                <ul class="navbar-links">
    <li class="nav-item">
        <a class="nav-link" href="reports.php">Reports</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="manage_users.php">Manage Users</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="landing.html">Logout</a>
    </li>
</ul>
                  
                </li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Customer Care Dashboard</h1> <!-- Page heading -->
        <?php if (!empty($grouped_issues)): ?> <!-- Check if there are issues -->
            <?php foreach ($grouped_issues as $status => $issues): ?> <!-- Loop through grouped issues -->
                <div class="status-group">
                    <h2 class="status-title <?php echo str_replace(' ', '_', $status); ?>"> <!-- Display status title -->
                        <?php echo ucfirst($status); ?> Issues <!-- Display capitalized status -->
                    </h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Student</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($issues as $issue): ?> <!-- Loop through each issue -->
                                <tr>
                                    <td><?php echo $issue['issue_id']; ?></td> <!-- Display issue ID -->
                                    <td><?php echo $issue['issue_title']; ?></td> <!-- Display issue title -->
                                    <td><?php echo $issue['issue_description']; ?></td> <!-- Display issue description -->
                                    <td><?php echo $issue['first_name'] . ' ' . $issue['last_name']; ?></td> <!-- Display student name -->
                                    <td>
                                        <div class="action-form">
                                            <form method="POST">
                                                <input type="hidden" name="issue_id" value="<?php echo $issue['issue_id']; ?>"> <!-- Hidden field for issue ID -->
                                                <select name="forward_to" class="action-select">
                                                    <option value="">Forward to...</option>
                                                    <option value="lecturer">Lecturer</option>
                                                    <option value="hod">HOD</option>
                                                    <option value="dean">Dean</option>
                                                </select>
                                                <button type="submit" name="forward" class="action-button">Forward</button> <!-- Forward button -->
                                            </form>
                                            <form method="POST">
                                                <input type="hidden" name="issue_id" value="<?php echo $issue['issue_id']; ?>"> <!-- Hidden field for issue ID -->
                                                <select name="new_status" class="action-select">
                                                    <option value="">Update status...</option>
                                                    <option value="open">Open</option>
                                                    <option value="in progress">In Progress</option>
                                                    <option value="closed">Closed</option>
                                                </select>
                                                <button type="submit" name="update_status" class="action-button">Update</button> <!-- Update button -->
                                            </form>
                                            <button type="button" class="action-button respond-btn" data-issue-id="<?php echo $issue['issue_id']; ?>">Respond</button> <!-- Respond button -->
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No issues found.</p> <!-- Display message if no issues are found -->
        <?php endif; ?>
    </div>

    <div id="responseModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span> <!-- Close button for modal -->
            <h2>Respond to Issue</h2>
            <form id="responseForm" method="POST">
                <input type="hidden" name="issue_id" id="modalIssueId"> <!-- Hidden field for issue ID -->
                <textarea name="response_text" id="responseText" rows="4" cols="50" required></textarea> <!-- Text area for response -->
                <button type="submit" name="submit_response" class="action-button">Submit Response</button> <!-- Submit button for response -->
            </form>
        </div>
    </div>

    <script>
        var modal = document.getElementById("responseModal"); // Get the modal element
        var span = document.getElementsByClassName("close")[0]; // Get the close button
        var buttons = document.getElementsByClassName("respond-btn"); // Get all respond buttons

        // Add event listeners to respond buttons to open the modal
        for (var i = 0; i < buttons.length; i++) {
            buttons[i].onclick = function() {
                modal.style.display = "block";
                document.getElementById("modalIssueId").value = this.getAttribute("data-issue-id"); // Set issue ID in modal
            }
        }

        // Close the modal when the close button is clicked
        span.onclick = function() {
            modal.style.display = "none";
        }

        // Close the modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>

<?php
$stmt->close(); // Close the SQL statement
$conn->close(); // Close the database connection
?>
