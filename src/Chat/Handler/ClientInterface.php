<?php

namespace Chat\Handler;

interface ClientInterface
{
    public function onDataIn( $data );
    public function onDataOut( $data );
}