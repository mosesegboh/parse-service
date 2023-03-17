<?php

namespace App\Message;

class GetNewsMessage
{
    private $arg1;

    public function __construct(string $arg1)
    {
        $this->arg1 = $arg1;
    }

    public function getArg1(): string
    {
        return $this->arg1;
    }
}
