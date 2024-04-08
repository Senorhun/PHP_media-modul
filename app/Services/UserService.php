<?php

namespace App\Services;

use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserService
{

    public function findByApiKey($apiKey)
    {
        if (Gate::allows('admin')) {


            $foundUser = User::where("apiKey", $apiKey)->first();


            if (!$foundUser) {
                return response()->json(['message' => 'User not found'], 404);
            }
            $perPage = 10;
            $photos = $foundUser->photos()->paginate($perPage);

            if ($foundUser) {
                return response()->json([
                    'data' => $foundUser, $photos
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 404);
            }
        } else {
            return response()->json(['message' => 'Access denied', 'status' => false], 403);
        }
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
        if (Gate::allows('admin')) {


            $userToDelete = User::where('apiKey', $apiKey)->first();
            if (!$userToDelete) {
                return response()->json(['message' => 'User not found', 'status' => false], 404);
            } elseif ($userToDelete->status === 'deleted') {
                return response()->json(['message' => 'User is already soft-deleted', 'status' => false], 422);
            }
            $userToDelete->status = 'deleted';
            $userToDelete->save();
            return response()->json(['message' => 'User soft deleted successfully.', 'status' => true], 200);
        } else {
            return response()->json(['message' => 'Access denied', 'status' => false], 403);
        }
    }

    public function softUndeleteUser($apiKey)
    {

        if (Gate::allows('admin')) {

            $userToUndelete = User::where('apiKey', $apiKey)->first();
            if (!$userToUndelete) {
                return response()->json(['message' => 'User not found', 'status' => false], 404);
            } elseif ($userToUndelete->status === 'active') {
                return response()->json(['message' => 'User is already active', 'status' => false], 422);
            }
            $userToUndelete->status = 'active';
            $userToUndelete->save();
            return response()->json(['message' => 'Image soft undeleted successfully.', 'status' => true], 200);
        } else {
            return response()->json(['message' => 'Access denied', 'status' => false], 403);
        }
    }

    public function listDeletedUsers()
    {
        if (Gate::allows('admin')) {
            $perPage = 25;
            $users = User::where('status', 'deleted')->paginate($perPage);

            return response()->json($users);
        } else {
            return response()->json(['message' => 'Access denied.'], 403);
        }
    }
}
