<?php
    include "functions.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="../css/normalize.css" />
    <link rel="stylesheet" href="../css/styles.css" />
    <link rel="stylesheet" href="../css/stats-styles.css" />
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link
      href="https://fonts.googleapis.com/css2?family=Source+Code+Pro:wght@300;400;500;700&family=Source+Sans+Pro:wght@300;400;700&display=swap"
      rel="stylesheet"
    />
    <title>Statistiken</title>
</head>
<body>
    <header class="game-header">
        <a href="/MMP1/client/"><img src="../img/logo.svg" alt="darts.io Logo" class="logo" /></a>
    </header>
    <main>
        <div class="table-scroll">
            <table class="stats-table">
                <tr class="table-header">
                    <th class="text-cell">Punkte</th>
                    <th class="text-cell">In</th>
                    <th class="text-cell">Out</th>
                    <th colspan="2">Runden</th>
                    <th colspan="2">Average</th>
                </tr>
                <tr class="table-header">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="number-cell player-label">Sp. 1</td>
                    <td class="number-cell player-label">Sp. 2</td>
                    <td class="number-cell player-label">Sp. 1</td>
                    <td class="number-cell player-label">Sp. 2</td>
                </tr>
                <?php
                $sth = $dbh->query("SELECT * FROM game ORDER BY avg_player1 DESC LIMIT 100;");
                $games = $sth->fetchAll();
                foreach ($games as $game) {
                    ?>
                    <tr>
                        <td class="text-cell">  <?= $game['point_mode']     ?></td>
                        <td class="text-cell">  <?= $game['in_mode']        ?></td>
                        <td class="text-cell">  <?= $game['out_mode']       ?></td>
                        <td class="number-cell"><?= $game['rounds_player1'] ?></td>
                        <td class="number-cell"><?= $game['rounds_player2'] ?></td>
                        <td class="number-cell"><?= $game['avg_player1']    ?></td>
                        <td class="number-cell"><?= $game['avg_player2']    ?></td>
                    </tr>
                <?php
                }
                ?>
            </table>
        </div>
    </main>

    <footer>
        <p class="footer-text">© Martin Sonnberger 2021</p>
        <a href="imprint" class="footer-link">Impressum</a>
    </footer>
</body>
</html>