<?php 
$i=0;
// Define connection parameters
$host = "localhost";
$username = "clinic_user";
$password = "UserClinicConnect";
$database = "clinic";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = "SELECT docID, firstName, lastName FROM doctors";
$result = $conn->query($sql);

// Check and display results
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $docID=$row["docID"];
        $firstName=$row["firstName"];
        $lastName=$row["lastName"];
        
        $id[$i]=$docID;
        $fName[$i]=$firstName;
        $lName[$i]=$lastName;
        $i++;

   
    }
} else {
    echo "0 results";
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Nice Fill-In Form</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f6f9;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .form-container {
      background: #fff;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
    }

    h2 {
      margin-bottom: 20px;
      color: #333;
      text-align: center;
    }

    label {
      display: block;
      margin-bottom: 6px;
      font-weight: bold;
      color: #555;
    }

    input, select, textarea {
      width: 100%;
      padding: 12px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
      transition: border-color 0.3s;
    }

    input:focus, select:focus, textarea:focus {
      border-color: #007bff;
      outline: none;
    }

    button {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 12px;
      width: 100%;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    button:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>

<div class="form-container">
  <h2>Fill-In Form</h2>
  <form>
   
    <select id="doctors" required>
      <option value="" disabled selected>Select your doctor</option>
      <?php
      for($j=0;$j<$i;$j++){
        echo "<option value='$id[$j]'>".$fName[$j]." ".$lastName[$j]."</option>";
      }
      ?>
    </select>

   
   

    <button type="submit">Submit</button>
  </form>
</div>

</body>
</html>
