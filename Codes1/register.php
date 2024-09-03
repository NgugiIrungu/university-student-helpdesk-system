<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
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
        .register-form {
            max-width: 400px;
            padding: 20px;
            border: 1px solid #eaeaea;
            border-radius: 4px;
            background-color: #fff;
        }
        .register-form h2 {
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
        .alert-danger {
            color: red;
            margin-bottom: 10px;
        }
    </style>
    <script>
        function validateForm() {
            var username = document.getElementById("username").value;
            var password = document.getElementById("password").value;
            var firstName = document.getElementById("first_name").value;
            var lastName = document.getElementById("last_name").value;
            var email = document.getElementById("email").value;
            var role = document.getElementById("role").value;
            var errors = [];

            // Check if any required field is empty
            if (username === "" || password === "" || firstName === "" || lastName === "" || email === "" || role === "") {
                errors.push("Ensure All Fields Have Been Filled");
            }

            // Validate email format
            if (email.indexOf("@") === -1 || email.indexOf(".") === -1) {
                errors.push("Kindly Enter A Valid Email Address");
            }

            // Validate password length
            if (password.length < 8) {
                errors.push("Password must have at least 8 characters");
            }

            // Display errors if any
            if (errors.length > 0) {
                var errorContainer = document.getElementById("error-container");
                errorContainer.innerHTML = "";
                errors.forEach(function(error) {
                    var errorDiv = document.createElement("div");
                    errorDiv.className = "alert alert-danger";
                    errorDiv.innerText = error;
                    errorContainer.appendChild(errorDiv);
                });
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <div class="register-form">
        <h2>Register Here</h2>
        <div id="error-container"></div>
        
        <?php
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

    // Check if the form was submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve form data
        $username = $_POST["username"];
        // Hash the password using bcrypt
        $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
        $firstName = $_POST["first_name"];
        $lastName = $_POST["last_name"];
        $email = $_POST["email"];
        $role = $_POST["role"];

        // Start transaction
        $conn->begin_transaction();

        try {
            // Insert into user table
            $sql = "INSERT INTO user (username, password, first_name, last_name, email, role) VALUES ('$username', '$password', '$firstName', '$lastName', '$email', '$role')";
            if ($conn->query($sql)) {
                // Retrieve the auto-generated user ID
                $userID = $conn->insert_id;
                
                // If the role is student, insert into student table
                if ($role === "student") {
                    $sql_student = "INSERT INTO student (user_id, first_name, last_name, email) VALUES ($userID, '$firstName', '$lastName', '$email')";
                    if (!$conn->query($sql_student)) {
                        throw new Exception("Failed to insert into student table: " . $conn->error);
                    }
                }
                
                // Commit the transaction
                $conn->commit();
                
                echo "Successfully Registered. Your User ID is: $userID";
                // Redirect to respective dashboards based on roles
                switch ($role) {
                    case "student":
                        header("Location: dashboard.php");
                        exit();
                    case "lecturer":
                        header("Location: lecturer_dashboard.php");
                        exit();
                    case "customer_care":
                        header("Location: customercare_dash.php");
                        exit();
                    case "hod":
                        header("Location: hod_dashboard.php");
                        exit();
                    case "dean":
                        header("Location: dean_dashboard.php");
                        exit();
                    default:
                        header("Location: index.php");
                        exit();
                }
            } else {
                throw new Exception("Failed to insert into user table: " . $conn->error);
            }
        } catch (Exception $e) {
            // An error occurred, rollback the transaction
            $conn->rollback();
            echo "Oh No! Something Went Wrong! " . $e->getMessage();
        }
    }
    ?>
        <form method="POST" action="register.php" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" name="first_name" id="first_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" name="last_name" id="last_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="student">Student</option>
                    <option value="lecturer">Lecturer</option>
                    <option value="customer_care">Customer Care</option>
                    <option value="hod">HOD</option>
                    <option value="dean">Dean</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" name="submit" class="btn-primary">Register</button>
            </div>
        </form>
        <p class="text-center">Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>
</html>
