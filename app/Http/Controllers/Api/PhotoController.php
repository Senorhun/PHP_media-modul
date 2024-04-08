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
            return $this->photoService->findPhotoByFileName($fileName);
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
            return $this->photoService->findPhotoById($photoId);
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
            return $this->photoService->updatePhoto($request, $photoId);
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
            return $this->photoService->softDeletePhoto($photoId);
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
            return $this->photoService->softUndeletePhoto($photoId);
        } catch (TokenExpiredException | TokenBlacklistedException  $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }
}
