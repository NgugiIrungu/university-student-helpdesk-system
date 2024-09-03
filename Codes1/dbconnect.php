<?php
$servername = "localhost";
$username = "Project4th";
$password = "Assasination26";
$dbname = "customerdb";

// Create connection using conn variable to connect to database
$conn = new mysqli($servername, $username, $password, $dbname);

/* Check connection, if it is not created an error message should be displayed
Die is used to end execution of the script any further and displays the error
$conn->connect_error check if there was an error during the connection attempt.
*/
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    // echo "Connected successfully"; // Missing semicolon added here
}
 