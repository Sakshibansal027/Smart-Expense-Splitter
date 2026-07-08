<?php
// Local development credentials
$host = "localhost";
$username = "root";
$password = ""; 
$database = "splitter_db"; 

$conn = new mysqli($host, $username, $password, $database);


if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}
?>