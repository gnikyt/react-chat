<?php

namespace Chat\Command;

use Chat\User\Manager;
use Chat\User\User;

class Command
{
    private $user_manager,
            $user,
            $command,
            $data;

    public function __construct( Manager $user_manager, User $user )
    {
        $this->user_manager = $user_manager;
        $this->user         = $user;
        $this->command      = '';
        $this->data         = '';
    }

    public function setCommand( $command )
    {
        $this->command = ucfirst( $command ) . 'Command';

        return $this;
    }

    public function getCommand( )
    {
        return $this->command;
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

    public function parse( $message )
    {
        $data = explode( ' ', $message );
        $this->setCommand( substr( $data[ 0 ], 1 ) );

        array_shift( $data );
        $data = implode( ' ', $data );

        $this->setData( $data );
    }

    public function execute( )
    {
        $string = "Chat\\Command\\{$this->command}";
        $command = new $string( $this->user_manager, $this->user );
        $command->setData( $this->data );
        $command->execute( );
    }
}