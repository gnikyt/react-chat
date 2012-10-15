<?php

namespace Chat\Command;

use Chat\User\Manager;
use Chat\User\User;
use Chat\Handler\Message;

class PmCommand
{
    private $user_manager,
            $user,
            $data;

    public function __construct( Manager $user_manager, User $user )
    {
        $this->user_manager = $user_manager;
        $this->user         = $user;
    }

    public function setData( $data )
    {
        $this->data = $data;

        return $this;
    }

    public function getData( )
    {
        return $this->data;
    }

    public function getTo( )
    {
        $data = explode( ' ', $this->data );

        return trim( $data[ 0 ] );
    }

    public function getMessage( )
    {
        $data = explode( ' ', $this->data );
        array_shift( $data );

        return trim( implode( ' ', $data ) );
    }

    public function execute( )
    {
        $to         = $this->getTo( );
        $to_message = $this->getMessage( );

        $message = new Message;
        $message->setMessage( array(
            'from' => $this->user->getName( ),
            'message'   => $message->color(
                "[PM] {$to_message}\n",
                "red"
            )
        ));

        $encoded = $message->encode( );
        foreach( $this->user_manager as $current ) {
            if( strtolower( $current->getName( ) ) == strtolower( $to ) ) {
                $current->getConnection( )->write( $encoded . "\n" );
            }
        }
    }
}