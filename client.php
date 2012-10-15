<?php

require __DIR__.'/vendor/autoload.php';

use React\EventLoop\Factory;
use React\Stream\Stream;
use React\Socket\Connection;
use Chat\Handler\Client;

$loop       = Factory::create( );
$connection = new Connection( stream_socket_client( "tcp://{$argv[ 1 ]}/" ), $loop );
$stdin      = new Stream( STDIN, $loop );
$client     = new Client( $connection, $stdin );

$connection->on( 'data', array( $client, 'onDataIn' ) );
$stdin->on( 'data', array( $client, 'onDataOut' ) );

$loop->run( );