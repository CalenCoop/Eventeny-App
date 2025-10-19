<?php
require_once "database/database.php";
session_start();
$dbo = new Database();
$cmd = "SELECT * FROM tickets WHERE is_deleted = 0 ORDER BY created_at DESC";
$statement = $dbo->conn->prepare($cmd);
$statement-> execute();

$tickets = $statement->fetchAll(PDO::FETCH_ASSOC);
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Organizer Dashboard</title>
</head>
<body> 
    <div class="container">
        <h1>Event Organizer Dashboard</h1>
    </div>
    
</body>
</html>