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