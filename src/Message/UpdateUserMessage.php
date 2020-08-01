<?php

namespace App\Message;

final class UpdateUserMessage
{
    /*
     * Add whatever properties & methods you need to hold the
     * data for this message class.
     */
    private int $userId;
    private string $userData;

    public function __construct(int $userId, string $userData)
    {
        $this->userId = $userId;
        $this->userData = $userData;
    }

    public function getUserData(): string
    {
        return $this->userData;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}