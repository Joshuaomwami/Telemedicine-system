<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "patient") {
    header("Location: login.php");
    exit();
}
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $appt_id = intval($_POST["appointment_id"]);
    $new_time = $_POST["new_time"];
    $user_id = $_SESSION["user_id"];

    // Update only if pending or confirmed
    $sql = "UPDATE appointments 
            SET appointment_time=?, status='pending' 
            WHERE id=? AND patient_id=? AND status IN ('pending','confirmed')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $new_time, $appt_id, $user_id);

    if ($stmt->execute()) {
        $_SESSION["message"] = "Appointment rescheduled successfully.";
    } else {
        $_SESSION["message"] = "Error rescheduling appointment.";
    }
}
header("Location: patient-dashboard.php");
exit();
