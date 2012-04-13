<?php

function getStats( $substituteNames = true, $filename = 'openarena.log' ) {
    $file = @fopen( $filename, 'r' );
    if (!$file ) {
        return array();
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
                $player_guid = $playerdata[23];
                $player_ids[ $client_id ]['name'] = $player_name;
                $player_ids[ $client_id ]['guid'] = $player_guid;

                if (!isset( $player_stats[ $player_guid ] )) {
                    $player_stats[$player_guid] = array( 'names' => array( $player_name ), 'items' => array(), 'kills' => array(), 'killed_by' => array(), 'kills_who' => array(), 'kills_method' => array(), 'killed_by_who' => array(), 'killed_by_method' => array(), 'suicides' => array(), 'rounds' => 1, 'awards' => array() );
                } else {
                    $player_stats[$player_guid]['rounds']++;
                    $player_stats[$player_guid]['names'][] = $player_name;
                    $player_stats[$player_guid]['names'] = array_unique( $player_stats[$player_guid]['names'] );
                }
            } break;
            case 'Item:': {
                $client_id = $words[1];
                $item = $words[2];
                $player = $player_ids[$client_id]['guid'];
                $item_parts = explode('_',$item);
                $item_type = $item_parts[0];

                @$player_stats[$player]['items'][$item_type][$item]++;
            } break;
            case 'Kill:': {
                $victim_id = $words[2];
                $attacker_id = ( $words[1] == 1022 ? $victim_id : $words[1]);
                $method = $words[8];
            
                $attacker = $player_ids[$attacker_id]['guid'];
                $victim = $player_ids[$victim_id]['guid'];

                if ( $victim_id == $attacker_id ) {
                    @$player_stats[$victim]['suicides'][$method]++;
                } else {
                    @$player_stats[$victim]['killed_by'][$attacker][$method]++;
                    @$player_stats[$attacker]['kills'][$victim][$method]++;
                }
            } break;
            case 'Award:': {
                $player_id = $words[1];
                $award = $words[6];
                $player = $player_ids[$player_id]['guid'];
    
                @$player_stats[$player]['awards'][$award]++;
            } break;

        }
    }

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
    
    if ( $substituteNames ) {
        $player_stats = substituteNames( $player_stats );
    }
    return $player_stats;
}

function substituteNames( $stats, $useRealNames = true ) {
    function setNames( $array, $map ) {
        $new_array = array();

        foreach( $array as $key => $value ) {
            if ( isset( $map[$key] ) ) {
                $key = $map[$key];
            }

            if ( is_array( $value ) ) {
                $value = setNames( $value, $map );
            } elseif ( isset( $map[$value] ) ) {
                $value = $map[$value];
            }

            $new_array[$key] = $value;
        }

        return $new_array;
    }
    $player_map = array();
    
    if ( $useRealNames ) {
        include( 'real_names.php' );
    } else {
        foreach( $stats as $guid => $player ) {
            $player_map[ $guid ] = $player['names'][0];
        }
    }

    $stats = setNames( $stats, $player_map );

    return $stats;
}

/* 
 * Usage
 * $player_stats = substituteNames( getStats( false ) );
 * $player_stats = getStats();
 * print_r( $player_stats );
 * die(0);
 */
