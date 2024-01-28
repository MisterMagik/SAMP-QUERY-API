<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Samp API example</title>
</head>
<body>
    <?php
        require_once "../sampapi.php";

        $SampAPI = new SampQueryApi("127.0.0.1", 7777);
        $SampAPI->connect();
        echo "<br>";
        echo "Password (0/1): ".$SampAPI->getPasswordInfo()."<br>Hostname: ".$SampAPI->getHostname()."<br>Online players: ".$SampAPI->getPlayersOnline()."<br>Max players: ".$SampAPI->getMaxPlayers()."<br>Gamemode: ".$SampAPI->getGamemode();
        echo "<br>Language: ".$SampAPI->getLanguage();
        $SampAPI->close();
    ?>
    
</body>
</html>