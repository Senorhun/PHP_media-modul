<?php

namespace App\Services;

use App\Models\User;

class UserService
{

    public function findByApiKey($apiKey)
    {
        $foundUser = User::where("apiKey", $apiKey)->first();
        return $foundUser;
    }

    public function findByName($firstName, $lastName)
    {
        $foundUsers = User::where('firstName', $firstName)->where('lastName', $lastName)->get();
        return $foundUsers;
    }
}
