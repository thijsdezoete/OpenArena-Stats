<?php
include_once( 'stats-include.php' );

$stats = getStats();

$rocket_stats = array();

foreach ( $stats as $player => $data ) {
    $rocket_kills = (int)@$data['kills_method']['MOD_ROCKET'];
    $splash_kills = (int)@$data['kills_method']['MOD_ROCKET_SPLASH'];
    $accuracy = ( $splash_kills == 0 ) ? 'infinity' : $rocket_kills / $splash_kills;
    $rocket_stats[ $player ] = array( 'MOD_ROCKET' => $rocket_kills, 'MOD_ROCKET_SPLASH' => $splash_kills, 'accuracy' => $accuracy );
}

print_r( $rocket_stats );
