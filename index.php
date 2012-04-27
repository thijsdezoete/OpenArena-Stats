<?php include('stats.php'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>OpenArena :: Statistics</title>
    <link rel="stylesheet" href="/css/reset.css">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

    <div id="wrapper">

        <header>
            <h1>OpenArena Statistics</h1>
            <?php if (isset($_GET['player']) && array_key_exists($_GET['player'], $stats)) : ?>
                <h2><?php echo $_GET['player']; ?></h2>
            <?php else: ?>
                <h2>&nbsp;</h2>
            <?php endif; ?>
            <nav>
                <?php foreach ($stats as $name => $info) : ?>
                    <?php if (isset($_GET['player']) && $name == $_GET['player']) : ?>
                    <a href="?player=<?php echo $name; ?>" class="active"><?php echo $name; ?></a>
                    <?php else: ?>
                    <a href="?player=<?php echo $name; ?>"><?php echo $name; ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </header>

        <?php if (isset($_GET['player']) && array_key_exists($_GET['player'], $stats)) : ?>
            <pre><?php echo print_r($stats[$_GET['player']], TRUE); ?></pre>
        <?php else: ?>
            <div id="select"><p>Select a player from the list above to see his/her statistics.</p></div>
        <?php endif; ?>

    </div>

</body>
</html>
