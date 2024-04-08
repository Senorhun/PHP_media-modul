<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\Input;

class ApiController extends Controller
{

    public function register(Request $request)
    {
        $request->validate([
            'firstName' => "required",
            'lastName' => "required",
            'email' => "required|email|unique:users",
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
            ],
            'password_confirmation' => ['required', 'same:password'],
        ]);


        User::create([
            "firstName" => $request->firstName,
            "lastName" => $request->lastName,
            "email" => $request->email,
            "password" => Hash::make($request->password)
        ]);


        return response()->json([
            "status" => true,
            "message" => "User created succesfully"
        ]);
    }

    public function login(Request $request)
    {

        $request->validate([
            "email" => "required|email",
            "password" => "required"
        ]);

        $token = JWTAuth::attempt([
            "email" => $request->email,
            "password" => $request->password
        ]);

        if (!empty($token)) {

            return response()->json([
                "status" => true,
                "message" => "User logged in succcessfully",
                "token" => $token
            ]);
        }


        return response()->json([
            "status" => false,
            "message" => "Invalid details"
        ]);
    }

    public function createUser(Request $request)
    {
        try {

            if (!$admin = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'Unauthenticated', 'status' => false], 401);
            }
            if (Gate::allows('admin')) {
                $email = $request->Input('email');
                $existingUser = User::where('email', $email)->exists();
                if ($existingUser) {
                    return response()->json(['message' => 'Email already in use', 'status' => false], 409);
                }
                $role = $request->Input('role');

                if ($role != 'user' && $role != 'admin') {
                    return response()->json(['message' => 'Role must be user or admin', 'status' => false], 409);
                }
                $validatedData = $request->validate([
                    'firstName' => 'required|string',
                    'lastName' => 'required|string',
                    'email' => 'required|email|unique:users',
                    'role' => 'required|in:admin,user',

                ]);

                $password = Str::random(8);


                $user = new User();
                $user->firstName = $validatedData['firstName'];
                $user->lastName = $validatedData['lastName'];
                $user->email = $validatedData['email'];
                $user->password = Hash::make($password);
                $user->role = $validatedData['role'];
                $user->save();

                return response()->json([
                    'message' => 'User created successfully.',
                    'password' => $password,
                ], 201);
            } else {
                return response()->json(['message' => 'Access denied', 'status' => false], 403);
            }
        } catch (TokenExpiredException | TokenBlacklistedException $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }

    public function listUsers()
    {
        try {

            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'Unauthenticated', 'status' => false], 401);
            }
            if (Gate::allows('admin')) {
                $perPage = 25;
                $users = User::where('status', 'active')->paginate($perPage);

                return response()->json($users);
            } else {
                return response()->json(['message' => 'Access denied', 'status' => false], 403);
            }
        } catch (TokenExpiredException | TokenBlacklistedException  $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }

    public function findByName($firstName, $lastName)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'User not found', 'status' => false], 404);
            }

            if (Gate::allows('admin')) {

                if (!$firstName || !$lastName) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Both firstName and lastName are required fields'
                    ], 422);
                }

                $users = User::where('firstName', $firstName)->where('lastName', $lastName)->get();

                if (!$users->isEmpty()) {
                    return response()->json([
                        'data' => $users
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
        } catch (TokenExpiredException | TokenBlacklistedException $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }

    public function findByApiKey($apiKey)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'User not found', 'status' => false], 404);
            }

            if (Gate::allows('admin')) {

                if (!$apiKey) {
                    return response()->json([
                        'status' => false,
                        'message' => 'ApiKey is the required field'
                    ], 422);
                }


                $foundUser = User::where('apiKey', $apiKey)->first();
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
        } catch (TokenExpiredException | TokenBlacklistedException $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }
    public function softDeleteUser(Request $request)
    {
        try {
            if (!$request->has('apiKey')) {
                return response()->json(['message' => 'API key is required', 'status' => false], 400);
            }
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'Unauthenticated', 'status' => false], 401);
            }
            if (Gate::allows('admin')) {
                $apiKey = $request->input('apiKey');

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
        } catch (TokenExpiredException | TokenBlacklistedException  $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }



    public function softUndeleteUser(Request $request)
    {
        try {
            if (!$request->has('apiKey')) {
                return response()->json(['message' => 'API key is required', 'status' => false], 400);
            }
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'Unauthenticated', 'status' => false], 401);
            }
            if (Gate::allows('admin')) {
                $apiKey = $request->input('apiKey');

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
        } catch (TokenExpiredException | TokenBlacklistedException  $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }


    public function listDeletedUsers()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'User not found', 'status' => false], 404);
            }
            if (Gate::allows('admin')) {
                $perPage = 25;
                $users = User::where('status', 'deleted')->paginate($perPage);

                return response()->json($users);
            } else {
                return response()->json(['message' => 'Access denied.'], 403);
            }
        } catch (TokenExpiredException | TokenBlacklistedException $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }




    public function profile()
    {

        $userdata = auth()->user();

        return response()->json([
            "status" => true,
            "message" => "Profile data",
            "data" => $userdata
        ]);
    }
    public function refreshToken()
    {
        $token = JWTAuth::getToken();

        $newToken = JWTAuth::refresh($token);


        JWTAuth::invalidate($token);
        return response()->json([
            "status" => true,
            "message" => "New access token",
            "token" => $newToken
        ]);
    }
    public function logout()
    {

        auth()->logout();

        JWTAuth::getToken();
        JWTAuth::invalidate($forever = true);

        return response()->json([
            "status" => true,
            "message" => "User logged out successfully"
        ]);
    }
}
