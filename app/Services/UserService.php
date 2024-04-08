<?php

namespace App\Services;

use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserService
{

    public function findByApiKey($apiKey)
    {
        $foundUser = User::where("apiKey", $apiKey)->first();
        return $foundUser;
    }

    public function findByName($firstName, $lastName)
    {
        if (Gate::allows('admin')) {


            $foundUsers = User::where('firstName', $firstName)->where('lastName', $lastName)->get();


            if (!$foundUsers->isEmpty()) {
                return response()->json([
                    'data' => $foundUsers
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 404);
            }
        } else {
            return response()->json(['message' => 'Access denied.'], 403);
        }
    }

    public function listUsers()
    {

        if (Gate::allows('admin')) {
            $perPage = 25;
            $users = User::where('status', 'active')->paginate($perPage);

            return response()->json($users);
        } else {
            return response()->json(['message' => 'Access denied', 'status' => false], 403);
        }
    }




    public function softDeleteUser($apiKey)
    {
        // Your logic for soft deleting a user here
    }

    public function softUndeleteUser($apiKey)
    {
        // Your logic for soft undeleting a user here
    }

    public function listDeletedUsers()
    {
        // Your logic for listing deleted users here
    }
}
