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

// Check if user is logged in and is a customer care representative
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer_care') {
    die("Error: Unauthorized access. This page is only for customer care representatives.");
}

// Handle user actions (add or delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        $email = $_POST['email'];

        $sql = "INSERT INTO user (username, password, role, email) VALUES ('$username', '$password', '$role', '$email')";
        if ($conn->query($sql)) {
            $success_message = "User added successfully.";
        } else {
            $error_message = "Error adding user: " . $conn->error;
        }
    } elseif (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];

        $sql = "DELETE FROM user WHERE user_id = $user_id";
        if ($conn->query($sql)) {
            $success_message = "User deleted successfully.";
        } else {
            $error_message = "Error deleting user: " . $conn->error;
        }
    }
}

// Fetch all users
$sql = "SELECT user_id, username, role, email FROM user";
$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4; /*white smoke hexadecimal colour*/
        }

        header {
            background-color: #f0a608;/* vivid orange*/
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

        .user-form {
            background-color: #f9f9f9;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .user-form input,
        .user-form select {
            display: block;
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .user-form button,
        .delete-btn {
            background-color: #f0a608;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
        }

        .user-form button:hover,
        .delete-btn:hover {
            background-color: #d69407;
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

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a class="navbar-brand" href="customercare_dash.php">
                <img src="logo.png" alt="Logo" class="navbar-logo">
                Catholic University of Eastern Africa
            </a>
            <ul class="navbar-links">
                <li class="nav-item">
                    <a class="nav-link" href="customercare_dash.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php">Reports</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="landing.html">Logout</a>
                </li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Manage Users</h1>
        
        <?php
        if (isset($success_message)) {
            echo "<div class='message success'>$success_message</div>";
        }
        if (isset($error_message)) {
            echo "<div class='message error'>$error_message</div>";
        }
        ?>
        
        <div class="user-form">
            <h2>Add New User</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="email" name="email" placeholder="Email" required>
                <select name="role" required>
                    <option value="student">Student</option>
                    <option value="lecturer">Lecturer</option>
                    <option value="hod">HOD</option>
                    <option value="dean">Dean</option>
                    <option value="customer_care">Customer Care</option>
                </select>
                <button type="submit" name="add_user">Add User</button>
            </form>
        </div>

        <h2>User List</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo ($user['user_id']); ?></td>
                        <td><?php echo ($user['username']); ?></td>
                        <td><?php echo ($user['role']); ?></td>
                        <td><?php echo ($user['email']); ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <button type="submit" name="delete_user" class="delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>