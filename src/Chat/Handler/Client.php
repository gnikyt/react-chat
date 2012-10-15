<?php

namespace Chat\Handler;

use React\Socket\Connection;
use React\Stream\Stream;
use Chat\Handler\ClientInterface;

class Client implements ClientInterface
{
    private $connection,
            $stdin,
            $user;

    public function __construct( Connection $connection, Stream $stdin )
    {
        $this->connection   = $connection;
        $this->stdin        = $stdin;
        $this->id           = 0;
    }

    public function onDataIn( $data )
    {
        if( strstr( $data, 'Your ID:' ) ) {
            preg_match( '#ID: (.*)#i', $data, $matches );
            if( !empty( $matches[ 1 ] ) ) {
                $this->id = trim( $matches[ 1 ] );
            }
        }

        echo $data;
    }

    public function onDataOut( $data )
    {
        if( $this->id > 0 ) {
            $this->connection->write( "{$this->id}[::]{$data}" );
        } else {
            $this->connection->write( $data );
        }
    }
}