<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey - Rate 1-10</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Survey Results</h1>
        
        <div class="survey-box">
            <form id="surveyForm" method="POST" action="vote.php">
                <div class="number-grid" role="radiogroup" aria-label="Select a number from 1 to 10">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <label class="number-option">
                            <input type="radio" name="rating" value="<?php echo $i; ?>" required aria-label="Number <?php echo $i; ?>">
                            <span class="number-circle"><?php echo $i; ?></span>
                        </label>
                    <?php endfor; ?>
                </div>
                
                <button type="submit" id="submitBtn" class="submit-btn" disabled aria-label="Submit your rating">
                    SUBMIT
                </button>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
