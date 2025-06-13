<?php
$host = "localhost:3307";
$dbname = "mieleza";
$user = "root";
$pass = ""; // update with your actual DB password

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
