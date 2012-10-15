<?php

namespace Chat\Handler;

use React\Socket\Connection;
use React\Stream\Stream;
use Chat\Handler\ClientInterface;
use Chat\Handler\Message;

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
        $messages   = explode( "\n", $data );
        $rest       = "\n" . array_pop( $messages );

        foreach( $messages AS $message ) {
            $_message   = new Message( $message );
            $_data      = $_message->decode( );

            if( $_data->from == 'server' ) {
                if( strstr( $_data->message, 'Your ID:' ) ) {
                    $this->setClientId( $_data->message );
                }
            }

            echo "{$_data->from}: {$_data->message}\n";
        }

        $data = $rest . $data;
    }

    public function onDataOut( $data )
    {
        $message = new Message( array(
            'from'      => $this->id,
            'message'   => $data
        ));
        $this->connection->write( $message->encode( ) );
    }

    public function setClientId( $data )
    {
        preg_match( '#ID: (.*)#i', $data, $matches );
        if( !empty( $matches[ 1 ] ) ) {
            $this->id = trim( $matches[ 1 ] );
        } else {
            $this->id = trim( $data );
        }
    }

    public function getId( )
    {
        return $this->id;
    }
}