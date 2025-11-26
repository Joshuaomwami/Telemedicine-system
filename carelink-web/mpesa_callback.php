<?php
require_once "db.php";

// Get appointment_id from URL
$appointment_id = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;

// Get callback data (from Safaricom / Sandbox)
$data = file_get_contents("php://input");
$logFile = "mpesa_log.json"; // log for debugging
file_put_contents($logFile, $data . PHP_EOL, FILE_APPEND);

$response = json_decode($data, true);

// Default values (useful if testing without real M-Pesa)
$mpesa_receipt = "TEST123";
$amount = 0;
$phone = "254708374149";
$status = "success";

// ✅ If real response from Safaricom
if (isset($response["Body"]["stkCallback"]["ResultCode"])) {
    $resultCode = $response["Body"]["stkCallback"]["ResultCode"];

    if ($resultCode == 0) { // payment successful
        $status = "success";

        $callbackMetadata = $response["Body"]["stkCallback"]["CallbackMetadata"]["Item"];

        foreach ($callbackMetadata as $item) {
            if ($item["Name"] == "Amount") {
                $amount = $item["Value"];
            }
            if ($item["Name"] == "MpesaReceiptNumber") {
                $mpesa_receipt = $item["Value"];
            }
            if ($item["Name"] == "PhoneNumber") {
                $phone = $item["Value"];
            }
        }
    } else {
        $status = "failed";
    }
}

// ✅ If appointment is valid
if ($appointment_id > 0) {
    // Get patient_id from appointments
    $stmt = $conn->prepare("SELECT patient_id FROM appointments WHERE id=?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $appt = $result->fetch_assoc();

    if ($appt) {
        $patient_id = $appt["patient_id"];

        // Insert into payments table
        $stmt = $conn->prepare("INSERT INTO payments (appointment_id, patient_id, amount, mpesa_receipt, phone_number, status) 
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iidsss", $appointment_id, $patient_id, $amount, $mpesa_receipt, $phone, $status);
        $stmt->execute();

        // If success, mark appointment as confirmed
        if ($status == "success") {
            $stmt = $conn->prepare("UPDATE appointments SET status='confirmed' WHERE id=?");
            $stmt->bind_param("i", $appointment_id);
            $stmt->execute();
        }
    }
}

// ✅ Always return response to Safaricom (important!)
echo json_encode(["ResultCode" => 0, "ResultDesc" => "Accepted"]);
