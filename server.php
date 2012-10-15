<?php

require __DIR__.'/vendor/autoload.php';

use React\EventLoop\Factory;
use React\Socket\Server;
use Chat\User\Manager;
use Chat\Handler\Server as ServerHandler;

$loop           = Factory::create( );
$socket         = new Server( $loop );
$user_manager   = new Manager;
$handler        = new ServerHandler( $user_manager, $loop );

$socket->on( 'connection', array( $handler, 'onConnection' ) );

$port = ( @$argv[ 1 ] ) ?: 4000;
echo "Socket server listening on port {$port}.\n";
echo "You can connect to it by running: php client.php localhost:{$port}\n";
$socket->listen( $port );
$loop->run( );