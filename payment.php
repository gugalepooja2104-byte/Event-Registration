<?php
include(__DIR__ . '/db.php');

$data = json_decode(file_get_contents("php://input"));

$name = $data->name;
$event = $data->event;
$txnId = $data->txnId;

$sql = "UPDATE registrations 
        SET transaction_id='$txnId', payment_status='Pending'
        WHERE name='$name' AND event_name='$event'
        ORDER BY id DESC LIMIT 1";

if ($conn->query($sql) === TRUE) {
    echo "Payment Updated";
} else {
    echo "Error";
}
?>