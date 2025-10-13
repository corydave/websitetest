<?php
// vote.php - Handles vote submission

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Get and validate the rating
$rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);

if ($rating === false || $rating < 1 || $rating > 10) {
    header('Location: index.php');
    exit;
}

// Path to data file
$dataFile = 'data.json';

// Initialize data structure
$data = [
    'total' => 0,
    'votes' => array_fill(1, 10, 0)
];

// Read existing data if file exists
if (file_exists($dataFile)) {
    $jsonData = file_get_contents($dataFile);
    $existingData = json_decode($jsonData, true);
    
    if ($existingData !== null) {
        $data = $existingData;
    }
}

// Add the new vote
$data['votes'][$rating]++;
$data['total']++;

// Save updated data
file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));

// Redirect to results page
header('Location: results.php');
exit;
?>
