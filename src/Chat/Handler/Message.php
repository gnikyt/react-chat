<?php

namespace Chat\Handler;

use Chat\Handler\MessageInterface;

class Message implements MessageInterface
{
    private $message,
            $colors;

    public function __construct( $message = '' )
    {
        $this->message = $message;
        $this->colors  = array(
                            'purple'    => '1;35',
                            'red'       => '0;31',
                            'green'     => '1;32'
                        );

        return $this;
    }

    public function setMessage( $message )
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage( )
    {
        return $this->message;
    }

    public function decode( )
    {
        return json_decode( $this->message );
    }

    public function encode( )
    {
        return json_encode( $this->message );
    }

    public function color( $message, $color )
    {
        return "\033[{$this->colors[ $color ]}m{$message}\033[0m";
    }
}