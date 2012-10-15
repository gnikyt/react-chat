<?php

namespace Chat\Handler;

interface MessageInterface
{
    public function __construct( $message = '' );
    public function setMessage( $message );
    public function getMessage( );
    public function decode( );
    public function encode( );
}