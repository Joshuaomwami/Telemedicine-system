<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "patient") {
    header("Location: login.php");
    exit();
}
include "db.php";

if (isset($_GET["id"])) {
    $appt_id = intval($_GET["id"]);
    $user_id = $_SESSION["user_id"];

    // Ensure appointment belongs to patient
    $sql = "UPDATE appointments 
            SET status='cancelled' 
            WHERE id=? AND patient_id=? AND status IN ('pending','confirmed')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $appt_id, $user_id);

    if ($stmt->execute()) {
        $_SESSION["message"] = "Appointment cancelled successfully.";
    } else {
        $_SESSION["message"] = "Error cancelling appointment.";
    }
}
header("Location: patient-dashboard.php");
exit();
