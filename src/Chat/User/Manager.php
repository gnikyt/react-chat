<?php

namespace Chat\User;

class Manager implements \IteratorAggregate
{
    private $users;

    public function addUser( User $user )
    {
        $this->users[ (int) $user->getId( ) ] = $user;

        return $this;
    }

    public function getUser( $id )
    {
        return $this->users[ (int) $id ];
    }

    public function removeUser( $id )
    {
        unset( $this->users[ (int) $id ] );
    }

    public function getIterator( )
    {
        return new \ArrayIterator( $this->users );
    }
}