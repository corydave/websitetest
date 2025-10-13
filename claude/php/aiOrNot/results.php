<?php
// results.php - Displays survey results

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

// Calculate maximum votes for scaling
$maxVotes = max($data['votes']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Results</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Survey Results</h1>
        
        <div class="results-box">
            <div class="total-responses">
                Total Responses: <?php echo $data['total']; ?>
            </div>
            
            <div class="results-list">
                <?php for ($i = 1; $i <= 10; $i++): 
                    $votes = $data['votes'][$i];
                    $percentage = $maxVotes > 0 ? ($votes / $maxVotes) * 100 : 0;
                    $isEmpty = $votes === 0;
                ?>
                    <div class="result-item">
                        <div class="result-number"><?php echo $i; ?></div>
                        <div class="result-bar-container">
                            <div class="result-bar <?php echo $isEmpty ? 'empty' : ''; ?>" 
                                 data-width="<?php echo $percentage; ?>"
                                 style="width: 0%;"
                                 role="progressbar"
                                 aria-valuenow="<?php echo $votes; ?>"
                                 aria-valuemin="0"
                                 aria-valuemax="<?php echo $maxVotes; ?>"
                                 aria-label="Number <?php echo $i; ?>: <?php echo $votes; ?> votes">
                                <span class="result-count"><?php echo $votes; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
            
            <a href="index.php" class="back-link">← Vote Again</a>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
