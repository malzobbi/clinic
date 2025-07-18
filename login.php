<?php
session_start();

// Database credentials
//test
if(isset($_POST['login'])){
   
$host = 'localhost';
$db = 'clinic';
$user = 'apic';
$pass = '-BPqzHtpC3QBo.td';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user input
$username = $_POST['mail'];
$password = $_POST['pass'] ;

//convert the plain password into hashed password
$hashedPassword = hash('sha256', $password);
// Sanitize input
$username = trim($conn->real_escape_string($username));

// Query to get user
$sql = "SELECT username, password, position FROM login WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Check user
if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    
    // Verify password (assuming stored hashed password)
    if($hashedPassword==$row['password']){
        
    //if (password_verify($password, $row['password'])) {
        // Generate a secure token
        $token = bin2hex(random_bytes(32));

        // Save session with token
        $_SESSION['username'] = $row['username'];
        $_SESSION['token'] = $token;

        // Redirect
       $job_desc=$row['position'];
       print $job_desc;
        /*
       
        position or job description is:
        1. nurse
        2. staff nurse
        3. doctor
        4. lab nurse
        5. pathologist
        6. receptionist

       */
      switch($job_desc){
        case '1':
            header("Location: nurse_dashboard.php");
            break;
        case '2':
            header("Location: staffnurse_dashboard.php");
            break;

      }
        //header("Location: dashboard.php");
        exit;
    } else {
        echo "Invalid password.";
    }
} else {
    echo "User not found.";
}

// Close
$stmt->close();
$conn->close();
}

?>
