<?php

require_once __DIR__ . '/pyros_be/database.php';

try{
$db = Database::getConnection();
$sql = "SELECT * FROM variables WHERE project_id=:project_id";
$stmt = $db->prepare($sql);
$stmt->execute([
':project_id'=>'86bbhga1x'
]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($data[0]['json']);
}catch(PDOException $e){
print_r($e);
}
