<?php
include(__DIR__ . '/db.php');

$data = json_decode(file_get_contents("php://input"));

$name = $data->name;
$id = $data->id;
$email = $data->email;
$event = $data->event;
$fee = $data->fee;

$sql = "INSERT INTO registrations (name, student_id, email, event_name, fee)
        VALUES ('$name', '$id', '$email', '$event', '$fee')";

if ($conn->query($sql) === TRUE) {
    echo "Registered";
} else {
    echo "Error";
}
?>