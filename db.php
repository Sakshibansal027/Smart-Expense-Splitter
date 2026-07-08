<?php
$conn = new mysqli("localhost", "root", "1234", "splitter_db");

if ($conn->connect_error) {
    die(" Database Connection failed: " . $conn->connect_error);
}
?>