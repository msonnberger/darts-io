<?php
    include "functions.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiken</title>
</head>
<body>
    <table class="stats-table">
        <tr>
            <th>Punkte</th>
            <th>In-Modus</th>
            <th>Out-Modus</th>
            <th>Runden Spieler 1</th>
            <th>Runden Spieler 2</th>
            <th>Avg Spieler 1</th>
            <th>Avg Spieler 2</th>
        </tr>

        <?php
        $sth = $dbh->query("SELECT * FROM game;");
        $games = $sth->fetchAll();

        foreach ($games as $game) {
            ?>
            <tr>
                <td><?= $game['point_mode']     ?></td>
                <td><?= $game['in_mode']        ?></td>
                <td><?= $game['out_mode']       ?></td>
                <td><?= $game['rounds_player1'] ?></td>
                <td><?= $game['rounds_player2'] ?></td>
                <td><?= $game['avg_player1']    ?></td>
                <td><?= $game['avg_player2']    ?></td>
            </tr>
        <?php
        }
        ?>
    </table>
</body>
</html>