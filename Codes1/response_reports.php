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

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['customer_care', 'hod', 'dean', 'deputy_vice_chancellor', 'vice_chancellor'])) {
    die("Error: Unauthorized access. This page is only for authorized personnel.");
}

// Function to execute SQL query and return results
function executeQuery($conn, $sql, $params = []) {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Handle search
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$search_by = isset($_GET['search_by']) ? $_GET['search_by'] : 'all';

$search_sql = "SELECT r.response_id, r.issue_id, r.response_text, r.response_date, 
               i.issue_description, i.status, 
               s.student_id, s.email AS student_email,
               u.first_name AS student_first_name, u.last_name AS student_last_name,
               p.program_name, f.faculty_name,
               un.unit_name, un.unit_code,
               l.lecturer_name
               FROM response r
               JOIN issue i ON r.issue_id = i.issue_id
               JOIN student s ON i.student_id = s.student_id
               JOIN user u ON s.user_id = u.user_id
               JOIN program p ON s.program_id = p.program_id
               JOIN faculty f ON s.faculty_id = f.faculty_id
               JOIN units un ON i.unit_id = un.unit_id
               JOIN lecturer l ON un.lecturer_id = l.lecturer_id
               WHERE 1=1";

$search_params = [];

if (!empty($search_query)) {
    switch ($search_by) {
        case 'issue':
            $search_sql .= " AND i.issue_description LIKE ?";
            $search_params[] = "%$search_query%";
            break;
        case 'response':
            $search_sql .= " AND r.response_text LIKE ?";
            $search_params[] = "%$search_query%";
            break;
        case 'student':
            $search_sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR s.email LIKE ?)";
            $search_params[] = "%$search_query%";
            $search_params[] = "%$search_query%";
            $search_params[] = "%$search_query%";
            break;
        case 'program':
            $search_sql .= " AND p.program_name LIKE ?";
            $search_params[] = "%$search_query%";
            break;
        case 'unit':
            $search_sql .= " AND (un.unit_name LIKE ? OR un.unit_code LIKE ?)";
            $search_params[] = "%$search_query%";
            $search_params[] = "%$search_query%";
            break;
        case 'lecturer':
            $search_sql .= " AND l.lecturer_name LIKE ?";
            $search_params[] = "%$search_query%";
            break;
        default:
            $search_sql .= " AND (i.issue_description LIKE ? OR r.response_text LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR s.email LIKE ? OR p.program_name LIKE ? OR un.unit_name LIKE ? OR un.unit_code LIKE ? OR l.lecturer_name LIKE ?)";
            $search_params = array_fill(0, 9, "%$search_query%");
    }
}

$search_sql .= " ORDER BY r.response_date DESC";
$responses = executeQuery($conn, $search_sql, $search_params);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Response Reports</title>
    <style>
        /* ... (Keep the existing styles) ... */
    </style>
</head>
<body>
    <header>
        <!-- ... (Keep the existing header) ... -->
    </header>

    <div class="container">
        <h1>Response Reports</h1>
        
        <form class="search-form" method="GET">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search responses...">
            <select name="search_by">
                <option value="all" <?php echo $search_by == 'all' ? 'selected' : ''; ?>>All</option>
                <option value="issue" <?php echo $search_by == 'issue' ? 'selected' : ''; ?>>Issue</option>
                <option value="response" <?php echo $search_by == 'response' ? 'selected' : ''; ?>>Response</option>
                <option value="student" <?php echo $search_by == 'student' ? 'selected' : ''; ?>>Student</option>
                <option value="program" <?php echo $search_by == 'program' ? 'selected' : ''; ?>>Program</option>
                <option value="unit" <?php echo $search_by == 'unit' ? 'selected' : ''; ?>>Unit</option>
                <option value="lecturer" <?php echo $search_by == 'lecturer' ? 'selected' : ''; ?>>Lecturer</option>
            </select>
            <button type="submit">Search</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Response ID</th>
                    <th>Issue ID</th>
                    <th>Issue Description</th>
                    <th>Response Text</th>
                    <th>Response Date</th>
                    <th>Status</th>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>Student Email</th>
                    <th>Program</th>
                    <th>Faculty</th>
                    <th>Unit</th>
                    <th>Unit Code</th>
                    <th>Lecturer</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($responses as $response): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($response['response_id']); ?></td>
                        <td><?php echo htmlspecialchars($response['issue_id']); ?></td>
                        <td><?php echo htmlspecialchars($response['issue_description']); ?></td>
                        <td><?php echo htmlspecialchars($response['response_text']); ?></td>
                        <td><?php echo htmlspecialchars($response['response_date']); ?></td>
                        <td><?php echo htmlspecialchars($response['status']); ?></td>
                        <td><?php echo htmlspecialchars($response['student_id']); ?></td>
                        <td><?php echo htmlspecialchars($response['student_first_name'] . ' ' . $response['student_last_name']); ?></td>
                        <td><?php echo htmlspecialchars($response['student_email']); ?></td>
                        <td><?php echo htmlspecialchars($response['program_name']); ?></td>
                        <td><?php echo htmlspecialchars($response['faculty_name']); ?></td>
                        <td><?php echo htmlspecialchars($response['unit_name']); ?></td>
                        <td><?php echo htmlspecialchars($response['unit_code']); ?></td>
                        <td><?php echo htmlspecialchars($response['lecturer_name']); ?></td>
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