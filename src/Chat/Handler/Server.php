<?php

namespace Chat\Handler;

use React\Socket\Connection;
use Chat\Handler\ServerInterface;
use Chat\User\Manager;
use Chat\User\User;

class Server implements ServerInterface
{
    private $user_manager,
            $loop,
            $colors;

    public function __construct( Manager $user_manager, $loop )
    {
        $this->user_manager = $user_manager;
        $this->loop         = $loop;
        $this->colors       = array(
                                'purple'    => '1;35',
                                'red'       => '0;31',
                                'green'     => '1;32'
                            );

        return $this;
    }

    public function onConnection( Connection $connection )
    {
        $user = $this->registerNewUser( $connection );
        $this->write( $user->getConnection( ), "[server] What is your name? ", 'purple' );

        $self = $this;
        $user->getConnection( )->on( 'data', function( $data ) use( $self, $user ) {
            $self->onData( $data, $user );
        });
        $user->getConnection( )->on( 'end', function( ) use( $self, $user ){
            $self->onEnd( $user );
        });
    }

    private function registerNewUser( Connection $connection )
    {
        $user = new User;
        $user->setId( time( ) );
        $user->setConnection( $connection );
        $user->setAddress( $connection->getRemoteAddress( ) );

        $this->user_manager->addUser( $user );

        return $user;
    }

    public function onData( $data, User $user )
    {
        $from = $this->getMessageFrom( $data );
        foreach( $this->user_manager as $current ) {
            if( $user->getConnection( ) === $current->getConnection( ) ) {
                if( !$user->getName( ) ) {
                    $data = trim( $data );
                    $user->setName( $data );

                    $this->write( $user->getConnection( ), "[server] Welcome {$data}; Your ID: {$user->getId( )}\n", 'purple' );

                    $logged = array( );
                    foreach( $this->user_manager as $current ) {
                        if( $user->getConnection( ) != $current->getConnection( ) ) {
                            $this->write( $current->getConnection( ), "[server] {$data} has joined!\n", 'purple' );
                            $logged[ ] = $current->getName( );
                        }
                    }

                    $online = ( sizeof( $logged ) > 0 ) ? implode( ', ', $logged ) : 'Just you!';
                    $this->write( $user->getConnection( ), "[server] Users online: {$online}\n", 'purple' );

                    continue;
                }

                if( $from[ 1 ]{0} == '/' ) {
                    $this->command( $user, $from[ 1 ] );
                }

                continue;
            }

            if( $from && $from[ 1 ]{0} !== '/' ) {
                $this->write( $current->getConnection( ), "{$from[ 0 ]->getName( )}: {$from[ 1 ]}\n", 'green' );
            }
        }
    }

    public function onEnd( User $user )
    {
        $id     = $user->getId( );
        $name   = $user->getName( );
        $this->user_manager->removeUser( $id );

        foreach( $this->user_manager as $u ) {
            $this->write( $u->getConnection( ), "[server] {$name} has disconnected.\n", 'purple' );
        }
    }

    private function write( Connection $connection, $message, $color = 'green' )
    {
        return $connection->write( $this->colorMessage( $message, $color ) );
    }

    private function colorMessage( $message, $color )
    {
        return "\033[{$this->colors[ $color ]}m{$message}\033[0m";
    }

    private function getMessageFrom( $data )
    {
        $from = null;
        if( strstr( $data, '[::]' ) ) {
            list( $id, $data ) = explode( '[::]', $data );

            $from = array(
                        $this->user_manager->getUser( $id ),
                        $data
                    );
        }

        return $from;
    }

    private function command( User $user, $command )
    {
        $data     = explode( ' ', $command );
        $command  = substr( $data[ 0 ], 1 ) . 'Command';

        if( is_callable( array( $this, $command ) ) ) {
            array_shift( $data );
            return $this->$command( $user, $data );
        }

        return $this->write( $user->getConnection( ), "[server] Command: {$data[ 0 ]} is unknown.\n", 'purple' );
    }

    private function renameCommand( User $user, $data )
    {
        $old_name   = $user->getName( );
        $new_name   = trim( $data[ 0 ] );
        $user->setName( $new_name );

        foreach( $this->user_manager as $current ) {
            $this->write( $current->getConnection( ), "[server] {$old_name} is now known as {$new_name}\n", 'purple' );
        }
    }

    private function pmCommand( User $user, $data )
    {
        $to_name = $data[ 0 ];
        array_shift( $data );
        $message = trim( implode( ' ', $data ) );

        foreach( $this->user_manager as $current ) {
            if( strtolower( $current->getName( ) ) == strtolower( $to_name ) ) {
                $this->write( $current->getConnection( ), "[PM from {$user->getName( )}] {$message}\n", 'red' );
            }
        }
    }
}