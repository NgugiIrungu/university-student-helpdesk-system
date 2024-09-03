<?php
session_start(); // Start the session to access session variables

// Database connection details
$servername = "localhost"; // Database server name
$username = "Project4th"; // Database username
$password = "Assasination26"; // Database password
$dbname = "customerdb"; // Database name

$conn = new mysqli($servername, $username, $password, $dbname); // Create a new MySQLi connection

if ($conn->connect_error) { // Check if the connection failed
    die("Connection failed: " . $conn->connect_error); // Output error message and stop script execution if connection failed
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) { // Check if user_id session variable is not set
    header("Location: login.php"); // Redirect to login page if user is not logged in
    exit(); // Stop further script execution
}

$user_id = $_SESSION['user_id']; // Get user_id from session

// Fetch user profile
$sql = "SELECT student_id, first_name, last_name, email FROM student WHERE user_id = '$user_id'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Fetch issue counts
$sql = "SELECT 
    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_issues,
    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_issues
    FROM issue WHERE student_id = '{$user['student_id']}'";
$result = $conn->query($sql);
$issue_counts = $result->fetch_assoc();

// Fetch recent issues
$sql = "SELECT issue_id, issue_title, status FROM issue WHERE student_id = '{$user['student_id']}' ORDER BY issue_id DESC LIMIT 3";
$recent_issues = $conn->query($sql);

$conn->close(); // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> <!-- Character encoding -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive design -->
    <title>Student Dashboard</title> <!-- Page title -->
    <style>
        body {
            margin: 0; /* Remove default margin */
            padding: 0; /* Remove default padding */
            font-family: Arial, sans-serif; /* Set font family */
            background-color: #f4f7fa; /* Background color */
            color: #333; /* Text color */
        }

        header {
            background-color: #f0a608; /* Header background color */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Add shadow to header */
            position: fixed; /* Fix header to the top */
            width: 100%; /* Full width */
            z-index: 1000; /* Ensure header is above other elements */
            top: 0; /* Align header to the top */
        }

        .navbar {
            display: flex; /* Flexbox layout */
            justify-content: space-between; /* Space between items */
            align-items: center; /* Center items vertically */
            padding: 15px 20px; /* Padding around navbar */
            max-width: 1200px; /* Maximum width */
            margin: 0 auto; /* Center align */
        }

        .navbar-brand {
            display: flex; /* Flexbox layout */
            align-items: center; /* Center items vertically */
            text-decoration: none; /* Remove underline */
            color: #000000; /* Text color */
            font-weight: bold; /* Bold text */
            font-size: 1.2em; /* Font size */
        }

        .navbar-logo {
            height: 40px; /* Logo height */
            margin-right: 10px; /* Space between logo and text */
        }

        .navbar-nav {
            list-style-type: none; /* Remove list bullets */
            display: flex; /* Flexbox layout */
            margin: 0; /* Remove margin */
            padding: 0; /* Remove padding */
        }

        .nav-item {
            margin-left: 20px; /* Space between nav items */
        }

        .nav-link {
            text-decoration: none; /* Remove underline */
            color: #000000; /* Text color */
            font-weight: bold; /* Bold text */
            transition: color 0.3s ease; /* Smooth color transition */
        }

        .nav-link:hover {
            color: #ffffff; /* Change color on hover */
        }

        .container {
            max-width: 1200px; /* Maximum width */
            margin: 100px auto 0; /* Center align and space from top */
            padding: 20px; /* Padding around container */
            display: grid; /* Grid layout */
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); /* Responsive columns */
            gap: 20px; /* Space between grid items */
        }

        .dashboard-content {
            background-color: #ffffff; /* Background color */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Add shadow */
            border-radius: 8px; /* Rounded corners */
            padding: 20px; /* Padding inside box */
            transition: transform 0.3s ease; /* Smooth transform transition */
        }

        .dashboard-content:hover {
            transform: translateY(-5px); /* Move up on hover */
        }

        h1 {
            color: #333333; /* Heading color */
            text-align: center; /* Center align */
            margin-bottom: 30px; /* Space below heading */
        }

        h2 {
            color: #f0a608; /* Subheading color */
            margin-bottom: 15px; /* Space below subheading */
            border-bottom: 2px solid #f0a608; /* Border below subheading */
            padding-bottom: 5px; /* Space below subheading text */
        }

        ul {
            list-style: none; /* Remove list bullets */
            padding: 0; /* Remove padding */
        }

        li {
            margin-bottom: 10px; /* Space below list items */
        }

        a {
            text-decoration: none; /* Remove underline */
            color: #007bff; /* Link color */
            font-weight: bold; /* Bold text */
            transition: color 0.3s ease; /* Smooth color transition */
        }

        a:hover {
            color: #0056b3; /* Change color on hover */
        }

        .btn {
            display: inline-block; /* Inline block layout */
            padding: 10px 15px; /* Padding inside button */
            background-color: #f0a608; /* Background color */
            color: #ffffff; /* Text color */
            border-radius: 5px; /* Rounded corners */
            text-align: center; /* Center align text */
            transition: background-color 0.3s ease; /* Smooth background transition */
        }

        .btn:hover {
            background-color: #e09600; /* Change background on hover */
        }

        .logout-btn {
            background-color: #dc3545; /* Background color for logout button */
        }

        .logout-btn:hover {
            background-color: #c82333; /* Change background on hover */
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr; /* Single column layout on small screens */
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <a class="navbar-brand" href="#">
                <img src="logo.png" alt="Logo" class="navbar-logo"> <!-- Logo image -->
                CUEA Student Portal
            </a>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="Dashboard.php">Dashboard</a> <!-- Link to Dashboard -->
                </li>
                <li class="nav-item">
                    <a class="nav-link logout-btn btn" href="landing.html">Logout</a> <!-- Logout button -->
                </li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Welcome to Your Student Dashboard, <?php echo ($user['first_name'] . ' ' . $user['last_name']); ?></h1> <!-- Display user's name -->

        <div class="dashboard-content">
            <h2>Quick Actions</h2>
            <ul>
                <li><a href="submit_issue.php" class="btn">Submit an Issue</a></li> <!-- Link to submit an issue -->
                <li><a href="view_issue.php" class="btn">View My Issues</a></li> <!-- Link to view issues -->
                <li><a href="Response.php" class="btn">Check Responses</a></li> <!-- Link to check responses -->
            </ul>
        </div>

        <div class="dashboard-content">
            <h2>Overview</h2>
            <ul>
                <li>Open Issues: <span id="total-open-issues"><?php echo $issue_counts['open_issues']; ?></span></li> <!-- Display open issues count -->
                <li>Resolved Issues: <span id="total-resolved-issues"><?php echo $issue_counts['resolved_issues']; ?></span></li> <!-- Display resolved issues count -->
                <li>Announcements: <span id="important-announcements">Check your email for updates</span></li> <!-- Placeholder for announcements -->
            </ul>
            <a href="issues_reports.php">View Common Q&A</a> <!-- Link to common Q&A -->
        </div>

        <div class="dashboard-content">
            <h2>My Recent Issues</h2>
            <ul id="my-issues-list">
                <?php while ($issue = $recent_issues->fetch_assoc()): ?> <!-- Loop through recent issues -->
                    <li>Issue #<?php echo $issue['issue_id']; ?>: <?php echo ($issue['issue_title']); ?> - <?php echo ucfirst($issue['status']); ?></li> <!-- Display issue details -->
                <?php endwhile; ?>
            </ul>
            <a href="view_issue.php">View All Issues</a> <!-- Link to view all issues -->
        </div>

        <div class="dashboard-content">
            <h2>User Profile</h2>
            <p>Name: <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p> <!-- Display user's name -->
            <p>Student ID: <?php echo htmlspecialchars($user['student_id']); ?></p> <!-- Display student's ID -->
            <p>Email: <?php echo htmlspecialchars($user['email']); ?></p> <!-- Display user's email -->
            <!-- <a href="#" class="btn">Edit Profile</a> Link to edit profile -->
        </div>
    </div>
</body>
</html>
