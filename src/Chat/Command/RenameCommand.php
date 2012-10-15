<?php

namespace Chat\Command;

use Chat\User\Manager;
use Chat\User\User;
use Chat\Handler\Message;

class RenameCommand
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

    public function getOldName( )
    {
        return $this->user->getName( );
    }

    public function getNewName( )
    {
        $data = explode( ' ', $this->data );

        return trim( $data[ 0 ] );
    }

    public function execute( )
    {
        $old_name   = $this->getOldName( );
        $new_name   = $this->getNewName( );
        $this->user->setName( $new_name );

        $message = new Message;
        $message->setMessage( array(
            'from' => 'server',
            'message'   => $message->color(
                "[server] {$old_name} is now known as {$new_name}\n",
                "purple"
            )
        ));

        $encoded = $message->encode( );
        foreach( $this->user_manager as $current ) {
            $current->getConnection( )->write( $encoded . "\n" );
        }
    }
}