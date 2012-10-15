<?php

namespace Chat\Handler;

use React\Socket\Connection;
use Chat\Handler\ServerInterface;
use Chat\Handler\Message;
use Chat\User\Manager;
use Chat\User\User;
use Chat\Command\Command;

class Server implements ServerInterface
{
    private $user_manager,
            $loop;

    public function __construct( Manager $user_manager, $loop )
    {
        $this->user_manager = $user_manager;
        $this->loop         = $loop;

        return $this;
    }

    public function onConnection( Connection $connection )
    {
        $user = $this->registerNewUser( $connection );

        $message = new Message;
        $message->setMessage( array(
            'from'      => 'server',
            'message'   => $message->color(
                "What is your name? ",
                "purple"
            )
        ));
        $this->write( $user->getConnection( ), $message->encode( ) );

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
        $message    = new Message( $data );
        $decoded    = $message->decode( );

        if( $decoded->from == 0 ) {
            $name = $this->setUserName( $user, $decoded->message );

            $new_user_message    = new Message;
            $new_user_message->setMessage( array(
                'from'      => 'server',
                'message'   => $new_user_message->color(
                    "{$name} has joined the chat!\n",
                    "purple"
                )
            ));
            $new_user_message    = $new_user_message->encode( );

            foreach( $this->user_manager as $current ) {
                if( $user->getConnection( ) != $current->getConnection( ) ) {
                    $this->write( $current->getConnection( ), $new_user_message );
                }
            }

            $this->showLoggedInUsers( $user );
        } else {
            $decoded->from = $this->user_manager->getUser( $decoded->from )->getName( );

            $message    = new Message( $decoded );
            $encoded    = $message->encode( );

            foreach( $this->user_manager as $current ) {
                if( $user->getConnection( ) === $current->getConnection( ) ) {
                    if( $this->isCommand( $decoded->message ) ) {
                        $command = new Command( $this->user_manager, $user );
                        $command->parse( $decoded->message );
                        $command->execute( );
                    }

                    continue;
                }

                if( !$this->isCommand( $decoded->message ) ) {
                    $this->write( $current->getConnection( ), $encoded );
                }
            }
        }
    }

    public function onEnd( User $user )
    {
        $id     = $user->getId( );
        $name   = $user->getName( );
        $this->user_manager->removeUser( $id );

        $message = new Message;
        $message->setMessage( array(
            'from'      => 'server',
            'message'   => $message->color(
                "{$name} has disconnected.\n",
                "purple"
            )
        ));

        foreach( $this->user_manager as $u ) {
            $this->write( $u->getConnection( ), $message->encode( ) );
        }
    }

    private function write( Connection $connection, $message )
    {
        $connection->write( $message . "\n" );
    }

    private function setUserName( User $user, $name )
    {
        $name = trim( $name );
        $user->setName( $name );

        $message = new Message;
        $message->setMessage( array(
            'from'      => 'server',
            'message'   => $message->color(
                "Welcome {$name}; Your ID: {$user->getId( )}",
                "purple"
            )
        ));

        $this->write( $user->getConnection( ), $message->encode( ) );

        return $name;
    }

    private function showLoggedInUsers( User $user )
    {
        $logged = array( );
        foreach( $this->user_manager as $current ) {
            if( $user->getConnection( ) == $current->getConnection( ) ) {
                continue;
            }

            $logged[ ] = $current->getName( );
        }

        $count  = sizeof( $logged );
        $online = ( $count > 0 ) ? implode( ', ', $logged ) : 'Just you!';

        $message = new Message;
        $message->setMessage( array(
            'from'      => 'server',
            'message'   => $message->color(
                "{$count} users online: {$online}\n",
                "purple"
            )
        ));
        $this->write( $user->getConnection( ), $message->encode( ) );
    }

    private function isCommand( $data )
    {
        return ( $data{0} === '/' );
    }
}