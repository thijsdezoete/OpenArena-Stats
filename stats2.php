<?php

$filename = (@$argv[1] ? $argv[1] : 'openarena.log' );

$file = @fopen( $filename, 'r' );
if (!$file ) {
    echo "unable to open file: $filename\n";
    die(1);
}

$player_ids = array();
$player_stats = array();

while (( $line = fgets( $file, 4096 ) ) !== false ) {
    //strip \n
    $line = substr($line, 0, -1);

    $words = explode(' ', $line );

    switch ( $words[0] ) {
        case 'ClientUserinfoChanged:': {
            $client_id = $words[1];
            $playerdata = explode( '\\', str_replace( '\\\\', '|', $words[2] ) );
            $player_name = $playerdata[1];
            $player_ids[ $client_id ] = $player_name;

            if (!isset( $player_stats[ $player_name ] )) {
                $player_stats[$player_name] = array( 'items' => array(), 'kills' => array(), 'killed_by' => array(), 'kills_who' => array(), 'kills_method' => array(), 'killed_by_who' => array(), 'killed_by_method' => array(), 'suicides' => array(), 'rounds' => 1, 'awards' => array() );
            } else {
                $player_stats[$player_name]['rounds']++;
            }
        } break;
        case 'Item:': {
            $client_id = $words[1];
            $item = $words[2];
            $player_name = $player_ids[$client_id];
            $item_parts = explode('_',$item);
            $item_type = $item_parts[0];

            @$player_stats[$player_name]['items'][$item_type][$item]++;
        } break;
        case 'Kill:': {
            $victim_id = $words[2];
            $attacker_id = ( $words[1] == 1022 ? $victim_id : $words[1]);
            $method = $words[8];
            
            $attacker_name = $player_ids[$attacker_id];
            $victim_name = $player_ids[$victim_id];

            if ( $victim_id == $attacker_id ) {
                @$player_stats[$victim_name]['suicides'][$method]++;
            } else {
                @$player_stats[$victim_name]['killed_by'][$attacker_name][$method]++;
                @$player_stats[$attacker_name]['kills'][$victim_name][$method]++;
            }
        } break;
        case 'Award:': {
            $player_id = $words[1];
            $award = $words[6];
            $player_name = $player_ids[$player_id];

            @$player_stats[$player_name]['awards'][$award]++;
        } break;

    }
}

//Lists
foreach ($player_stats as &$player) {
    //kills who list
    foreach ( $player['kills'] as $victim => $data ) {
        foreach ( $data as $method => $count ) {
            @$player['kills_who'][$victim] += $count;
            @$player['kills_method'][$method] += $count;
        }
    }
    //killed by list
    foreach ( $player['killed_by'] as $victim => $data ) {
        foreach ( $data as $method => $count ) {
            @$player['killed_by_who'][$victim] += $count;
            @$player['killed_by_method'][$method] += $count;
        }
    }
}

//Sorting
foreach ($player_stats as &$stat ) {
    //items
    foreach ( $stat['items'] as &$item ) {
        array_multisort( $item, SORT_DESC | SORT_REGULAR );
    }
    
    //kills
    foreach ( $stat['kills'] as &$kill ) {
        array_multisort( $kill, SORT_DESC | SORT_REGULAR );
    }
    array_multisort( $stat['kills_who'], SORT_DESC | SORT_REGULAR );
    array_multisort( $stat['kills_method'], SORT_DESC | SORT_REGULAR );
    
    foreach ( $stat['killed_by'] as &$killed ) {
        array_multisort( $killed, SORT_DESC | SORT_REGULAR );
    }
    array_multisort( $stat['killed_by_who'], SORT_DESC | SORT_REGULAR );
    array_multisort( $stat['killed_by_method'], SORT_DESC | SORT_REGULAR );

    //awards
    array_multisort( $stat['awards'], SORT_DESC | SORT_REGULAR );
}

print_r( $player_stats );
die(0);
