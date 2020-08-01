<?php

namespace App\Message;

final class CreateUserMessage
{
    /*
     * Add whatever properties & methods you need to hold the
     * data for this message class.
     */

    private string $userData;

    public function __construct(string $userData)
    {
        $this->userData = $userData;
    }

    public function getUserData(): string
    {
        return $this->userData;
    }

}
