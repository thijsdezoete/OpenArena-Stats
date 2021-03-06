<?php

include('./players.php');

$starttime = microtime(true);

$dir    = '/var/log/openarena';

$files  = scandir($dir);
$logs   = array();
$size   = 0;
foreach($files as $file) {
    if(preg_match('/openarena_([0-9-]+)\.log/', $file)) {
        $logs[] = $file;
        $size   = $size + filesize($dir.'/'.$file);
    }
}

$stats = array();
foreach($logs as $log) {
    $h = fopen($dir.'/'.$log, 'r');
    while(($line = fgets($h, 4096)) !== false) {

        $line = substr($line, 0, -1);
        $parts = explode('|', $line);
        if (isset($parts[1])) {
            $parts = explode(': ', $parts[1]);
        } else {
            $parts = explode(': ', $line);
        }

        switch($parts[0]) {
            // Player info
            case 'ClientUserinfoChanged':
            break;

            // Player joins round
            case 'ClientBegin':

            break;

            // Capture the flag
            case 'CTF':
                $length = 5;
                $info   = array_reverse(explode(' ', $parts[2]));
                $total  = count($info);
                $player = getPlayerName($info, $length, $known_players);

                if (!isset($stats[$player]['CTF'])) {
                    //$stats[$player]['CTF']['fragged'] = 0;
                    //$stats[$player]['CTF']['got'] = 0;
                    $stats[$player]['CTF']['captured'] = 0;
                    //$stats[$player]['CTF']['returned'] = 0;
                }

                if($info[3] == 'captured') {
                    $stats[$player]['CTF'][$info[3]] += 1;
                }
            break;

            // Frags
            case 'Kill':
                if (preg_match('/([a-zA-Z0-9-_\ ]+)\ killed\ ([a-zA-Z0-9-_\ ]+)\ by\ ([A-Z_]+)/', $parts[2], $match)) {
                    $player = $known_players[$match[1]];
                    $victim = $known_players[$match[2]];
                    $weapon = $match[3];

                    if(!isset($stats[$player]['KILLS'])) {
                        $stats[$player]['KILLS']['frags'] = 0;
                        $stats[$player]['KILLS']['deaths'] = 0;
                        $stats[$player]['KILLS']['suicides'] = 0;
                    }

                    if(!isset($stats[$player]['WEAPONS'][$weapon])) {
                        $stats[$player]['WEAPONS'][$weapon] = 0;
                    }

                    if(!isset($stats[$player]['VICTIMS'][$victim])) {
                        $stats[$player]['VICTIMS'][$victim] = 0;
                    }

                    if(!isset($stats[$victim]['ENEMIES'][$player])) {
                        $stats[$victim]['ENEMIES'][$player] = 0;
                    }

                    $stats[$player]['VICTIMS'][$victim]++;
                    $stats[$victim]['ENEMIES'][$player]++;

                    if(!isset($stats[$victim]['KILLS'])) {
                        $stats[$victim]['KILLS']['frags'] = 0;
                        $stats[$victim]['KILLS']['deaths'] = 0;
                        $stats[$victim]['KILLS']['suicides'] = 0;
                    }

                    if ($player == $victim || $player == '<world>') {
                        $stats[$victim]['KILLS']['suicides']++;
                    } else {
                        $stats[$player]['KILLS']['frags']++;
                        $stats[$victim]['KILLS']['deaths']++;
                        $stats[$player]['WEAPONS'][$weapon]++;
                    }
                } else if (preg_match('/\<world\>\ killed\ ([a-zA-Z0-9-_\ ]+)\ by\ ([A-Z_]+)/', $parts[2], $match)) {
                    $player = $known_players[$match[1]];
                    if(!isset($stats[$player]['KILLS'])) {
                        $stats[$player]['KILLS']['frags'] = 0;
                        $stats[$player]['KILLS']['deaths'] = 0;
                        $stats[$player]['KILLS']['suicides'] = 0;
                    }
                    $stats[$player]['KILLS']['suicides']++;
                }
            break;

            // Awards
            case 'Award':
                $length = 5;
                $info   = array_reverse(explode(' ', $parts[2]));
                $total  = count($info);
                $player = getPlayerName($info, $length, $known_players);

                if (!isset($stats[$player]['AWARDS'])) {
                    $stats[$player]['AWARDS']['GAUNTLET'] = 0;
                    $stats[$player]['AWARDS']['IMPRESSIVE'] = 0;
                    $stats[$player]['AWARDS']['EXCELLENT'] = 0;
                    $stats[$player]['AWARDS']['CAPTURE'] = 0;
                    $stats[$player]['AWARDS']['ASSIST'] = 0;
                    $stats[$player]['AWARDS']['DEFENCE'] = 0;
                }

                $stats[$player]['AWARDS'][$info[1]] += 1;
            break;
        }

    }
    fclose($h);
}

function getPlayerName($info, $minLength, $known_players) {
    $player = '';

    if (count($info) == $minLength) {
        $player = $info[count($info)-1];
    } else if (count($info) > $minLength) {
        for($i = count($info)-1; $i >= $minLength-1; $i--) {
            $player .= $info[$i].' ';
        }
        $player = substr($player, 0, -1);
    } else {
        $player = 'Unkown';
    }

    if (empty($player)) {
        $player = 'Unknown';
    }

    if(isset($known_players[$player])) {
        $player = $known_players[$player];
    }

    return trim($player);
}

// Sort by name
uksort($stats, 'strnatcmp');

foreach($stats as $player => $info) {
    // Sort awards
    arsort($stats[$player]['AWARDS']);

    // Sort weapons
    arsort($stats[$player]['WEAPONS']);

    // Sort victims
    arsort($stats[$player]['VICTIMS']);

    // Sort enemies
    arsort($stats[$player]['ENEMIES']);

    // Calculate K/D ratio
    $stats[$player]['KILLS']['ratio'] = number_format($stats[$player]['KILLS']['frags'] / $stats[$player]['KILLS']['deaths'], 2);
}

$endtime = microtime(true);

$totaltime = ($endtime - $starttime);
