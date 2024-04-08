<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Photo;
use App\Services\PhotoService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Illuminate\Support\Facades\Gate;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;

class PhotoController extends Controller
{

    protected $photoService;
    public function __construct(PhotoService $photoService)
    {
        $this->photoService = $photoService;
    }
    public function upload(Request $request)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'User not found', 'status' => false], 404);
            }
            return $this->photoService->upload($request, $user);
        } catch (TokenExpiredException | TokenBlacklistedException  $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }

    public function multiUpload(Request $request)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'User not found', 'status' => false], 404);
            }
            return $this->photoService->multiUpload($request, $user);
        } catch (TokenExpiredException | TokenBlacklistedException  $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }

    public function listPhotos()
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'User not found', 'status' => false], 404);
            }
            return $this->photoService->listPhotos();
        } catch (TokenExpiredException | TokenBlacklistedException  $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }
    public function listDeletedPhotos()
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'User not found', 'status' => false], 404);
            }
            return $this->photoService->listDeletedPhotos();
        } catch (TokenExpiredException | TokenBlacklistedException  $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }
    public function findPhotoByFileName($fileName)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'User not found', 'status' => false], 404);
            }
            if (Gate::allows('admin') || Gate::allows('user')) {

                if (!$fileName) {
                    return response()->json([
                        'status' => false,
                        'message' => 'FileName is the required field'
                    ], 422);
                }
                $images = Photo::where('fileName', $fileName)->where('status', 'active')->with('user:id,firstName,lastName')->get();
                if ($images->isEmpty()) {
                    return response()->json(['message' => 'No photo found.', 'status' => true], 200);
                }
                return response()->json(['data' => $images], 200);
            } else {
                return response()->json(['message' => 'Access denied.'], 403);
            }
        } catch (TokenExpiredException | TokenBlacklistedException  $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }

    public function findPhotoById($photoId)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'User not found', 'status' => false], 404);
            }
            if (Gate::allows('admin') || Gate::allows('user')) {

                $image = Photo::where('id', $photoId)->with('user:id,firstName,lastName')->first();
                if (!$image) {
                    return response()->json(['data' => null, 'message' => 'Image not found'], 404);
                }
                return response()->json(['data' => $image], 200);
            } else {
                return response()->json(['message' => 'Access denied.']);
            }
        } catch (TokenExpiredException | TokenBlacklistedException  $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }

    public function updatePhoto(Request $request, $photoId)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'User not found', 'status' => false], 404);
            }
            if (Gate::allows('admin')) {


                $image = Photo::find($photoId);
                if (!$image) {
                    return response()->json(['data' => null, 'message' => 'Image not found'], 404);
                }
                $validatedData = $request->validate([
                    'name' => 'nullable|string',
                    'description' => 'nullable|string',
                    'category' => 'nullable|string',
                    'keyword' => 'nullable|string',
                ]);


                $image->fill($validatedData);


                $image->save();

                return response()->json(['message' => 'Photo updated successfully.', 'status' => true], 200);
            } else {
                return response()->json(['message' => 'Access denied.'], 403);
            }
        } catch (TokenExpiredException | TokenBlacklistedException  $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }

    public function softDeletePhoto($photoId)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'Unauthenticated', 'status' => false], 401);
            }
            if (Gate::allows('admin')) {

                $image = Photo::find($photoId);
                if (!$image) {
                    return response()->json(['data' => null, 'message' => 'Image not found'], 404);
                } elseif ($image->status === 'deleted') {
                    return response()->json(['message' => 'Image is already soft-deleted', 'status' => false], 422);
                }
                $image->status = 'deleted';
                $image->save();
                return response()->json(['message' => 'Image soft deleted successfully.', 'status' => true], 200);
            } else {
                return response()->json(['data' => '', 'message' => 'Access denied.'], 403);
            }
        } catch (TokenExpiredException | TokenBlacklistedException  $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }
    public function softUndeletePhoto($photoId)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'Unauthenticated', 'status' => false], 401);
            }
            if (Gate::allows('admin')) {


                $image = Photo::find($photoId);
                if (!$image) {
                    return response()->json(['data' => null, 'message' => 'Photo not found'], 404);
                } elseif ($image->status === 'active') {
                    return response()->json(['message' => 'Photo is already active', 'status' => false], 422);
                }

                $image->status = 'active';
                $image->save();
                return response()->json(['message' => 'Photo soft undeleted successfully.', 'status' => true], 200);
            } else {
                return response()->json(['data' => '', 'message' => 'Access denied.'], 403);
            }
        } catch (TokenExpiredException | TokenBlacklistedException  $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }
}
