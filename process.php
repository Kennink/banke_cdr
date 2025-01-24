<?php
// Include Composer's autoloader
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Log function
function logMessage($message) {
    file_put_contents('process_log.txt', date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND);
}

logMessage("Process started.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve input values
    $value1 = isset($_POST['value1']) ? trim($_POST['value1']) : '';
    $value2 = isset($_POST['value2']) ? trim($_POST['value2']) : '';

    if (empty($value1) || empty($value2)) {
        $message = "Error: Both values are required.";
        logMessage($message);
        echo json_encode(['error' => $message]);
        exit;
    }

    // Path to the Excel file
    $filePath = __DIR__ . '/uploads/data.xlsx'; // Ensure the file is in the 'uploads' folder

    if (!file_exists($filePath)) {
        $message = "Error: Excel file not found.";
        logMessage($message);
        echo json_encode(['error' => $message]);
        exit;
    }

    // Log file found
    logMessage("Excel file found. Loading...");

    // Load the Excel file
    try {
        $spreadsheet = IOFactory::load($filePath);
        logMessage("Excel file loaded successfully.");
    } catch (Exception $e) {
        logMessage("Error loading Excel file: " . $e->getMessage());
        echo json_encode(['error' => 'Error loading the Excel file: ' . $e->getMessage()]);
        exit;
    }

    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    // Log: Rows read
    logMessage("Rows read from Excel file: " . count($rows) . " rows.");

    // Get headers and data rows
    $headers = $rows[0];
    $dataRows = array_slice($rows, 1);

    // Filter rows based on input values
    $filteredRows = array_filter($dataRows, function ($row) use ($value1, $value2) {
        return isset($row[0], $row[1]) && $row[0] == $value1 && $row[1] == $value2;
    });

    // Log filtered rows count
    logMessage("Filtered rows count: " . count($filteredRows));

    // Return filtered data
    if (!empty($filteredRows)) {
        $response = [];
        foreach ($filteredRows as $row) {
            $response[] = array_combine($headers, $row); // Combine headers with row values
        }
        logMessage("Returning filtered data.");
        echo json_encode($response);
    } else {
        $message = "No matching rows found.";
        logMessage($message);
        echo json_encode(['message' => $message]);
    }
    exit;
}
?>
