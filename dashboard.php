<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>YouTube Channel Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .dashboard-container {
            margin-top: 40px;
        }
        form {
            border: 2px solid black;
            padding: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container dashboard-container">
        <h1>YouTube Channel Dashboard</h1>

        <form method="post">
            <h3>Add a New YouTube Channel</h3>
            <div class="mb-3">
                <label for="channelId" class="form-label">Enter YouTube Channel ID:</label>
                <input type="text" name="channelId" id="channelId" class="form-control" placeholder="Channel ID" required>
            </div>
            <div class="mb-3">
                <label for="range" class="form-label">Enter Max Range:</label>
                <input type="number" name="range" id="range" class="form-control" placeholder="MAX RANGE" required>
            </div>
            <input type="submit" class="btn btn-success" value="Add Channel">
        </form>
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $channelId = trim($_POST['channelId']);
            $range=$_POST['range'];
            $escapedChannelId = escapeshellarg($channelId);
            $escapedRange = escapeshellarg($range);
            $command = "python3 fetch_youtube_data.py $escapedChannelId $escapedRange";
            $output = shell_exec($command);
            $collections = json_decode($output, true);
            if ($output !== null) {
                echo "<p>('Python file executed!')</p>";
            } else {
                echo "<p>('Error in Python file execution !')</p>";
            }
            // header("Location: http://localhost/mongo/dashboard.php");
            echo "<p>".$output."</p>";
        }
        ?>


        <form  method="post">
            <h3>View Existing Channels</h3>
            <div class="mb-3">
                <label for="existingChannels" class="form-label">Select a Channel:</label>
                <select name="existingChannels" id="existingChannels" class="form-control">
                <?php
                        foreach ($collections['collectionlist'] as $channelName => $channelId) {
                            echo "<option value='" . htmlspecialchars($channelId, ENT_QUOTES) . "'>" . htmlspecialchars($channelName, ENT_QUOTES) . "</option>";
                        }
                        ?>
                </select>
            </div>
            <input type="submit" class="btn btn-primary" value="View Data">
            
        </form>
        <?php
// Check if a POST request was made and the required field exists
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['existingChannels'])) {
    // Get the selected channel name and prepare the command to run the Python script
    $channel_name = trim($_POST['existingChannels']);
    $escaped_channel_name = escapeshellarg($channel_name);
    $command = "python3 mongoview.py $escaped_channel_name";
    
    // Execute the command and get the output
    $output = shell_exec($command);

    if ($output !== null && strlen($output) > 0) {
        // Decode the JSON output
        $decoded_output = json_decode($output, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            // Sum the statistics from the fetched data
            $total_views = 0;
            $total_likes = 0;
            $total_favorites = 0;
            $total_comments = 0;

            foreach ($decoded_output['documents'] as $doc) {
                if (isset($doc['viewCount'])) $total_views += intval($doc['viewCount']);
                if (isset($doc['likecounts'])) $total_likes += intval($doc['likecounts']);
                if (isset($doc['favoriteCount'])) $total_favorites += intval($doc['favoriteCount']);
                if (isset($doc['commentCount'])) $total_comments += intval($doc['commentCount']);
            }
            
            

            // Display the statistics in Bootstrap cards
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Channel Statistics</title>
                <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
            </head>
            <body>
                <div class="container">
                    <h2>Channel Statistics for <?php echo htmlspecialchars($channel_name); ?></h2>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card text-center mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Total Views</h5>
                                    <p class="card-text"><?php echo $total_views; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Total Likes</h5>
                                    <p class="card-text"><?php echo $total_likes; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Total Favorites</h5>
                                    <p class="card-text"><?php echo $total_favorites; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Total Comments</h5>
                                    <p class="card-text"><?php echo $total_comments; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            <?php
        } else {
            // Handle JSON decoding errors
            echo "<p>Error decoding JSON data.</p>";
        }
    } else {
        // Handle Python script execution errors
        echo "<p>Error executing the Python script or no output received.</p>";
    }
} else {
    echo "<p>No channel selected.</p>";
}
?>

 
    </div>
</body>
</html>
