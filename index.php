<?php include('stats.php'); 
    $player = isset($_GET['player']) ? $_GET['player'] : 'All';
    
    if ( $player == 'All' ) {
        $allstats = array();
        foreach ( $stats as $player ) {
            foreach ( $player as $stat => $data ) {
                foreach ( $data as $key => $value ) {
                    @$allstats[$stat][$key] += $value;
                }
            }
        }
        
        $allstats['KILLS']['ratio'] = $allstats['KILLS']['frags'] / ( $allstats['KILLS']['deaths'] + $allstats['KILLS']['suicides'] );
        
        reset( $stats );
        $stats['All'] = $allstats;
    }
?>
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

        <div id="main">
        <header>
            <h1>OpenArena Statistics</h1>
            <?php if (isset($player) && array_key_exists($player, $stats)) : ?>
                <h2><?php echo $player; ?></h2>
            <?php else: ?>
                <h2>&nbsp;</h2>
            <?php endif; ?>
            <nav>
                <?php foreach ($stats as $name => $info) : ?>
                    <?php if (isset($player) && $name == $player) : ?>
                    <a href="?player=<?php echo $name; ?>" class="active"><?php echo $name; ?></a>
                    <?php else: ?>
                    <a href="?player=<?php echo $name; ?>"><?php echo $name; ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </header>

        <?php if (isset($player) && array_key_exists($player, $stats)) : ?>
        <div id="content">
            <section id="kill">
                <div id="kills">
                    <h3>Kill stats</h3>
                    <table>
                    <?php
                    foreach($stats[$player]['KILLS'] as $stat => $amount) {
                        echo '<tr><td class="stat">'. ucfirst($stat) .'</td><td>'. $amount .'</td></tr>';
                    }
                    ?>
                    </table>
                </div>
                <div id="victims">
                    <h3>Victims</h3>
                    <table>
                    <?php
                    foreach($stats[$player]['VICTIMS'] as $victim => $amount) {
                        echo '<tr><td class="stat">'. $victim .'</td><td>'. $amount .'</td></tr>';
                    }
                    ?>
                    </table>
                </div>
                <div id="enemies">
                    <h3>Enemies</h3>
                    <table>
                    <?php
                    foreach($stats[$player]['ENEMIES'] as $enemy => $amount) {
                        echo '<tr><td class="stat">'. $enemy .'</td><td>'. $amount .'</td></tr>';
                    }
                    ?>
                    </table>
                </div>
                <div id="weapons">
                    <h3>Weapons</h3>
                    <table>
                    <?php
                    foreach($stats[$player]['WEAPONS'] as $weapon => $amount) {
                        echo '<tr><td class="stat">'. $weapon .'</td><td>'. $amount .'</td></tr>';
                    }
                    ?>
                    </table>
                </div>
            </section>

            <section id="awards">
                <h3>Awards</h3>
                <div><img src="/images/excellent.png" alt="Awarded when the player gains two frags within two seconds." title="Awarded when the player gains two frags within two seconds." width="65" height="65" /><span><?php echo (isset($stats[$player]['AWARDS']['EXCELLENT'])) ? $stats[$player]['AWARDS']['EXCELLENT'] : 0; ?></span></div>
                <div><img src="/images/impressive.png" alt="Awarded when the player achieves two consecutive hits with the railgun." title="Awarded when the player achieves two consecutive hits with the railgun." width="65" height="65" /><span><?php echo (isset($stats[$player]['AWARDS']['IMPRESSIVE'])) ? $stats[$player]['AWARDS']['IMPRESSIVE'] : 0; ?></span></div>
                <div><img src="/images/gauntlet.png" alt="Awarded when the player successfully frags someone with the gauntlet." title="Awarded when the player successfully frags someone with the gauntlet." width="65" height="65" /><span><?php echo (isset($stats[$player]['AWARDS']['GAUNTLET'])) ? $stats[$player]['AWARDS']['GAUNTLET'] : 0; ?></span></div>
                <div><img src="/images/capture.jpg" alt="Awarded when the player captures the flag." title="Awarded when the player captures the flag." width="65" height="65" /><span><?php echo (isset($stats[$player]['AWARDS']['CAPTURE'])) ? $stats[$player]['AWARDS']['CAPTURE'] : 0; ?></span></div>
                <div><img src="/images/assist.jpg" alt="Awarded when player returns the flag within ten seconds before a teammate makes a capture." title="Awarded when player returns the flag within ten seconds before a teammate makes a capture." width="65" height="65" /><span><?php echo (isset($stats[$player]['AWARDS']['ASSIST'])) ? $stats[$player]['AWARDS']['ASSIST'] : 0; ?></span></div>
                <div><img src="/images/defence.jpg" alt="Awarded when the player kills an enemy that was inside his base, or was hitting a team-mate that was carrying the flag." title="Awarded when the player kills an enemy that was inside his base, or was hitting a team-mate that was carrying the flag." width="65" height="65" /><span><?php echo (isset($stats[$player]['AWARDS']['DEFENCE'])) ? $stats[$player]['AWARDS']['DEFENCE'] : 0; ?></span></div>
            </section>
        </div>
        <?php else: ?>
            <div id="select"><p>Select a player from the list above to see his/her statistics.</p></div>
        <?php endif; ?>
        </div>

    </div>

    <footer><p>Parsed <?php echo count($logs); ?> logfiles (<?php echo round($size/1024, 1); ?> Kb) in <?php echo number_format($totaltime, 2); ?> seconds.</p></footer>

</body>
</html>
