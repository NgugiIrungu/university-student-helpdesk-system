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

$message = "";

if (!isset($_SESSION['user_id'])) {
    $message = "Error: User not logged in. Please log in to submit an issue.";
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $issue_title = $_POST['title'];
        $issue_description = $_POST['description'];
        $user_id = $_SESSION['user_id'];
        $status = 'open';

        // First, get the student_id based on the user_id
        $sql = "SELECT student_id FROM student WHERE user_id = '$user_id'";
        $result = $conn->query($sql);
        
        if ($row = $result->fetch_assoc()) {
            $student_id = $row['student_id'];
            
            // Now insert into the issue table using the correct student_id
            $sql = "INSERT INTO issue (student_id, issue_title, issue_description, status) VALUES ('$student_id', '$issue_title', '$issue_description', '$status')";

            if ($conn->query($sql)) {
                $message = "Issue submitted successfully!";
            } else {
                $message = "Error: " . $conn->error;
            }
        } else {
            $message = "Error: Student record not found for this user.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Issue</title>
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

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #555555;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #cccccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button[type="submit"] {
            padding: 10px 20px;
            background-color: #007bff;
            color: #ffffff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a class="navbar-brand" href="Dashboard.php">
                <img src="logo.png" alt="Logo" class="navbar-logo">
                Catholic University of Eastern Africa
            </a>
            <ul class="navbar-links">
                <li class="nav-item">
                    <a class="nav-link" href="Dashboard.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="view_issue.php">My Issues</a>
                </li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Submit an Issue</h1>
        <?php
        if (!empty($message)) {
            echo "<div class='message'>$message</div>";
        }
        ?>
        <form id="issueForm" method="POST" onsubmit="return validateForm()">
            <label for="title">Issue Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="description">Issue Description:</label>
            <textarea id="description" name="description" rows="4" required></textarea>

            <button type="submit">Submit</button>
        </form>
    </div>
    <script>
        function validateForm() {
            var title = document.getElementById("title").value;
            var description = document.getElementById("description").value;

            if (title.trim() === "" || description.trim() === "") {
                alert("All fields are required");
                return false;
            }

            return true;
        }
    </script>
</body>
</html>