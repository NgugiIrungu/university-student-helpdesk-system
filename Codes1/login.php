<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: url('school.jpeg'); 
            background-size: cover; 
            background-position: center;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .login-form {
            max-width: 400px;
            padding: 20px;
            border: 1px solid #eaeaea;
            border-radius: 4px;
            background-color: #fff;
        }
        .login-form h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        .form-control {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            height: 40px;
        }
        .btn-primary {
            width: 100%;
            background-color: #007bff;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .text-center {
            text-align: center;
            margin-top: 10px;
        }
    </style>
    <script>
        function validateForm() {
            var username = document.getElementById("username").value;
            var password = document.getElementById("password").value;

            if (username.trim() === "") {
                alert("Please enter your username.");
                return false;
            }

            if (password.trim() === "") {
                alert("Please enter your password.");
                return false;
            }

            return true;
        }
    </script>
</head>
<body>
    <div class="login-form">
        <h2>Login</h2>
        <form method="POST" action="login.php" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="form-group">
                <button type="submit" name="login" class="btn-primary">Login</button>
            </div>
        </form>
        <p class="text-center">Don't have an account? <a href="register.php">Register</a></p>
    </div>

    <?php
// Start the session
session_start();

// Database connection details
$servername = "localhost";
$username = "Project4th";
$password = "Assasination26";
$dbname = "customerdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form was submitted using the POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve username submitted via the form
    $username = $_POST["username"];
    // Retrieve password submitted via the form
    $password = $_POST["password"];

    // Define SQL query to retrieve user data based on the provided username
    $sql = "SELECT * FROM user WHERE username = '$username'";
    // Execute the SQL query
    $result = $conn->query($sql);

    // Check if the query returned any rows, indicating that the user was found
    if ($result->num_rows > 0) {
        // Fetch the row from the result set as an associative array
        $row = $result->fetch_assoc();
        // Verify the submitted password against the hashed password stored in the database
        if (password_verify($password, $row["password"])) {
            // Store user_id and role in session
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];

            // Redirect the user to the respective dashboard based on their role
            switch ($row['role']) {
                case "student":
                    header("Location: Dashboard.php");
                    break;
                case "lecturer":
                    header("Location: lecturer_dashboard.php");
                    break;
                case "customer_care":
                    header("Location: customercare_dash.php");
                    break;
                case "hod":
                    header("Location: hod_dashboard.php");
                    break;
                case "dean":
                    header("Location: dean_dashboard.php");
                    break;
                default:
                    header("Location: index.php");
                    break;
            }
            // Ensure no further processing is done
            exit();
        } else {
            // If the password doesn't match, display "Invalid password"
            echo "Invalid password";
        }
    } else {
        // If the user is not found in the database, display "User not found"
        echo "User not found";
    }
}

// Close the database connection
$conn->close();
?>
</body>
</html>