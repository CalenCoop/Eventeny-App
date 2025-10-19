<?php
require_once "database.php";

$dbo = new Database();

$cmd = "SELECT * FROM `tickets`";
$statement = $dbo->conn-> prepare($cmd);
$statement->execute();
$res = $statement-> fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($res);
echo "</pre>";
