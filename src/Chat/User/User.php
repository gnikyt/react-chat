<?php

namespace Chat\User;

use React\Socket\Connection;

class User
{
    private $connection,
            $name,
            $address,
            $id;

    public function setConnection( Connection $connection )
    {
        $this->connection = $connection;

        return $this;
    }

    public function getConnection( )
    {
        return $this->connection;
    }

    public function setId( $id )
    {
        $this->id = $id;

        return $id;
    }

    public function getId( )
    {
        return $this->id;
    }

    public function setName( $name )
    {
        $this->name = trim( $name );

        return $this;
    }

    public function getName( )
    {
        return $this->name;
    }

    public function setAddress( $address )
    {
        $this->address = $address;

        return $this;
    }

    public function getAddress( )
    {
        return $this->address;
    }
}