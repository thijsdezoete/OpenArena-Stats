<?php

$h = fopen('/home/jeroen/projects/openarena-stats/openarena.log', 'r');
$stats = array();
while(($line = fgets($h, 4096)) !== false) {
    // Game statistics
    if (preg_match('/([a-zA-Z0-9]+)\^7\ connected/', $line, $match)) {
        if (!isset($stats[$match[1]])) {
            $stats[$match[1]]['kills'] = 0;
            $stats[$match[1]]['deaths'] = 0;
            $stats[$match[1]]['suicides'] = 0;
            $stats[$match[1]]['games'] = 1;
        } else {
            $stats[$match[1]]['games']++;
        }
    }

    // Frag statistics
    if (preg_match('/([a-zA-Z0-9]+)\ killed\ ([a-zA-Z0-9]+)\ by\ ([A-Z_]+)/', $line, $match)) {
        $suicide = false;

        if (!isset($stats[$match[1]])) {
            $stats[$match[1]]['kills'] = 0;
            $stats[$match[1]]['deaths'] = 0;
            $stats[$match[1]]['suicides'] = 0;
            $stats[$match[1]]['games'] = 1;
        }

        if (!isset($stats[$match[2]])) {
            $stats[$match[2]]['kills'] = 0;
            $stats[$match[2]]['deaths'] = 0;
            $stats[$match[2]]['suicides'] = 0;
            $stats[$match[1]]['games'] = 1;
        }

        if($match[1] !== $match[2]) {
            $stats[$match[1]]['kills']++;
        } else {
            $stats[$match[1]]['suicides']++;
            $suicide = true;
        }

        if (!$suicide) {
            $stats[$match[2]]['deaths']++;
        }

        if (!isset($stats[$match[1]]['weapons'][$match[3]])) {
            $stats[$match[1]]['weapons'][$match[3]] = 1;
        } else {
            $stats[$match[1]]['weapons'][$match[3]]++;
        }
    }

    // Suicides
    if(preg_match('/\<world\>\ killed\ ([a-zA-Z0-9]+)/', $line, $match)) {
        if (!isset($stats[$match[1]])) {
            $stats[$match[1]]['kills'] = 0;
            $stats[$match[1]]['deaths'] = 0;
            $stats[$match[1]]['suicides'] = 1;
            $stats[$match[1]]['games'] = 1;
        } else {
            $stats[$match[1]]['suicides']++;
        }
    }
}
fclose($h);

// Calculate fun stuff!
foreach($stats as $name => $playerstat) {
    // K:D ratio
    if ($stats[$name]['kills'] == 0 && $stats[$name]['deaths'] == 0) {
        $stats[$name]['ratio'] = '0.00';
    }
    else if ($stats[$name]['deaths'] == 0 && $stats[$name]['kills'] > 0) {
        $stats[$name]['ratio'] = $stats[$name]['kills'].'.00';
    } else if ($stats[$name]['deaths'] > 0 && $stats[$name]['kills'] == 0) {
        $stats[$name]['ratio'] = -$stats[$name]['deaths'].'.00';
    } else {
        $stats[$name]['ratio'] = @number_format($stats[$name]['kills'] / $stats[$name]['deaths'], 2);
    }

    // Avg kills per game
    $stats[$name]['avg_kills_per_game'] = @number_format($stats[$name]['kills'] / $stats[$name]['games'], 2);

    // Avg deaths per game
    $stats[$name]['avg_deaths_per_game'] = @number_format($stats[$name]['deaths'] / $stats[$name]['games'], 2);

    if (isset($stats[$name]['weapons'])) {
        arsort($stats[$name]['weapons']);
        $fav_weapon = key($stats[$name]['weapons']);

        unset($stats[$name]['weapons']);
    } else {
        $fav_weapon = '?';
    }

    // Favorite weapon
    $stats[$name]['fav_weapon'] = $fav_weapon;
}

exit(print_r($stats, true));
