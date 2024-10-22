<?php
// fetch_parking_info.php
include 'db_config.php';  // ใช้การเชื่อมต่อฐานข้อมูลเดิม

// Get the latest 10 records
$stmt = $pdo->query("
    SELECT card.user_license_plate, lot.parked_zone, lot.number, lot.bay_id, bay.bay_name, card.card_id
    FROM card 
    INNER JOIN lot ON card.lot_id = lot.lot_id 
    INNER JOIN bay ON lot.bay_id = bay.bay_id 
    WHERE lot.status_id = '6'
    ORDER BY card.time DESC 
    LIMIT 10
");
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ส่งข้อมูลกลับในรูปแบบ JSON
echo json_encode($cards);
?>
