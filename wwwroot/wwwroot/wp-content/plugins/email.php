<!DOCTYPE HTML>
<html>
<head>
<style>
.error {color: #FF0000;}
</style>
</head>
<body>  

<?php
// define variables and set to empty values
$emailErr = "";
$email = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {  	
  if (empty($_POST["email"])) {
    $emailErr = "Email is required";
  } else {
    $email = test_input($_POST["email"]);
    // check if e-mail address is well-formed
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $emailErr = "Invalid email format";
    }
  }
    
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
?>

<?php

$usernames = array("user1", "user2", "user3", "user4", "");
if (in_array($_POST["email"],$usernames)) {
  	echo "Got it";
    }
  else{
    echo "Not";
    }
?>

</body>
</html>
