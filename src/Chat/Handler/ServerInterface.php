<?php

namespace Chat\Handler;

use React\Socket\Connection;
use Chat\User\User;

interface ServerInterface
{
    public function onConnection( Connection $connection );
    public function onData( $data, User $user );
    public function onEnd( User $user );
}