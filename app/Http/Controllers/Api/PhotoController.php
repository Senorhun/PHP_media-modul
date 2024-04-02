<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Photo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Illuminate\Support\Facades\Gate;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;

class PhotoController extends Controller
{


    public function upload(Request $request)
    {
        try {

            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'User not found', 'status' => false], 404);
            }
            if (Gate::allows('admin')) {
                $request->validate([
                    'image' => 'required|image|max:2048',
                ]);
                if ($request->hasFile('image')) {
                    $image = $request->file('image');

                    $fileName = $request->file('image')->getClientOriginalName();
                    $fileSize = $request->file('image')->getSize();
                    $extension = $request->file('image')->getClientOriginalExtension();
                    $mimeType = $request->file('image')->getMimeType();


                    $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
                    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                    $slug = Str::slug($fileNameWithoutExtension) . '-' . uniqid() . '.' . $fileExtension;

                    $photo = new Photo();
                    $photo->fileName = $fileName;
                    $photo->fileSize = $fileSize;
                    $photo->extension = $extension;
                    $photo->mimeType = $mimeType;
                    $photo->slug = $slug;
                    $photo->user_id = $user->id;

                    $photo->save();

                    $name = time() . '.' . $image->getClientOriginalExtension();
                    $path = public_path('upload');
                    $image->move($path, $name);
                    return response()->json(['message' => 'Image upload succesfull.', 'status' => true], 200);
                }
            } else {
                return response()->json(['data' => '', 'message' => 'Access denied.'], 403);
            }
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
            if (Gate::allows('admin')) {

                $request->validate([
                    'image.*' => 'required|image|max:2048',
                ]);
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    foreach ($image as $key => $value) {

                        $fileName = $value->getClientOriginalName();
                        $fileSize = $value->getSize();
                        $extension = $value->getClientOriginalExtension();
                        $mimeType = $value->getMimeType();

                        $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
                        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                        $slug = Str::slug($fileNameWithoutExtension) . '-' . uniqid() . '.' . $fileExtension;

                        $photo = new Photo();
                        $photo->fileName = $fileName;
                        $photo->fileSize = $fileSize;
                        $photo->extension = $extension;
                        $photo->mimeType = $mimeType;
                        $photo->slug = $slug;
                        $photo->user_id = $user->id;

                        $photo->save();


                        $name = time() . $key . '.' . $value->getClientOriginalExtension();
                        $path = public_path('upload');
                        $value->move($path, $name);
                    }
                    return response()->json(['message' => 'Image upload succesfull.', 'status' => true], 200);
                }
            } else {
                return response()->json(['message' => 'Access denied.'], 403);
            }
        } catch (TokenExpiredException | TokenBlacklistedException  $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }

    public function listPhotos()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'User not found', 'status' => false], 404);
            }
            if (Gate::allows('admin') || Gate::allows('user')) {

                $perPage = 25;
                $photos = Photo::where('status', 'active')->paginate($perPage);



                $imageData = $photos->map(function ($photo) {
                    return [
                        'id' => $photo->id,
                        'fileName' => $photo->fileName,
                        'fileSize' => $photo->fileSize,
                        'extension' => $photo->extension,
                        'mimeType' => $photo->mimeType,
                        'slug' => $photo->slug,
                        'user' => [
                            'id' => $photo->user->id,
                            'firstName' => $photo->user->firstName,
                            'lastName' => $photo->user->lastName,
                        ],
                    ];
                });


                return response()->json(['data' => $imageData, 'message' => 'List of images.', 'status' => true], 200);
            } else {
                return response()->json(['message' => 'Access denied.'], 403);
            }
        } catch (TokenExpiredException | TokenBlacklistedException  $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }
    public function listDeletedPhotos()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'User not found', 'status' => false], 404);
            }
            if (Gate::allows('admin')) {
                $perPage = 25;
                $photos = Photo::where('status', 'deleted')->paginate($perPage);



                $imageData = $photos->map(function ($photo) {
                    return [
                        'id' => $photo->id,
                        'fileName' => $photo->fileName,
                        'fileSize' => $photo->fileSize,
                        'extension' => $photo->extension,
                        'mimeType' => $photo->mimeType,
                        'slug' => $photo->slug,
                        'user' => [
                            'id' => $photo->user->id,
                            'firstName' => $photo->user->firstName,
                            'lastName' => $photo->user->lastName,
                        ],
                    ];
                });


                return response()->json(['data' => $imageData, 'message' => 'List of images.', 'status' => true], 200);
            } else {
                return response()->json(['message' => 'Access denied.']);
            }
        } catch (TokenExpiredException | TokenBlacklistedException  $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }
    public function findPhotoByFileName(Request $request)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'User not found', 'status' => false], 404);
            }
            if (Gate::allows('admin') || Gate::allows('user')) {
                $imageName = $request->input('fileName');
                if (!$imageName) {
                    return response()->json([
                        'status' => false,
                        'message' => 'FileName is the required field'
                    ], 422);
                }
                $images = Photo::where('fileName', $request->fileName)->where('status', 'active')->with('user:id,firstName,lastName')->get();
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

    public function findPhotoById(Request $request)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'User not found', 'status' => false], 404);
            }
            if (Gate::allows('admin') || Gate::allows('user')) {
                $imageId = $request->input('id');
                if (!$imageId) {
                    return response()->json([
                        'status' => false,
                        'message' => 'ImageId is the required field'
                    ], 422);
                }
                $image = Photo::where('id', $request->id)->with('user:id,firstName,lastName')->first();
                if (!$image) {
                    return response()->json(['data' => null, 'message' => 'Image not found'], 404);
                }
                return response()->json(['data' => $image], 200);
            } else {
                return response()->json(['data' => '', 'message' => 'Access denied.']);
            }
        } catch (TokenExpiredException | TokenBlacklistedException  $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }

    public function updatePhoto(Request $request)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'User not found', 'status' => false], 404);
            }
            if (Gate::allows('admin')) {

                $imageId = $request->input('id');
                if (!$imageId) {
                    return response()->json([
                        'status' => false,
                        'message' => 'ImageId is the required field'
                    ], 422);
                }

                $image = Photo::find($request->id);
                if (!$image) {
                    return response()->json(['data' => null, 'message' => 'Image not found'], 404);
                }
                $validatedData = $request->validate([
                    'name' => 'nullable|string',
                    'description' => 'nullable|string',
                    'category' => 'nullable|string',
                    'keyword' => 'nullable|string',
                ]);

                // Fill only the validated fields
                $image->fill($validatedData);

                // Save the updated record
                $image->save();

                return response()->json(['message' => 'Photo updated successfully.', 'status' => true], 200);
            } else {
                return response()->json(['message' => 'Access denied.'], 403);
            }
        } catch (TokenExpiredException | TokenBlacklistedException  $e) {
            return response()->json(['message' => 'Token has expired', 'status' => false], 401);
        }
    }

    public function softDeletePhoto(Request $request)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'Unauthenticated', 'status' => false], 401);
            }
            if (Gate::allows('admin')) {

                $imageId = $request->input('id');
                if (!$imageId) {
                    return response()->json([
                        'status' => false,
                        'message' => 'ImageId is the required field'
                    ], 422);
                }

                $image = Photo::find($request->id);
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
    public function softUndeletePhoto(Request $request)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'Unauthenticated', 'status' => false], 401);
            }
            if (Gate::allows('admin')) {

                $imageId = $request->input('id');
                if (!$imageId) {
                    return response()->json([
                        'status' => false,
                        'message' => 'ImageId is the required field'
                    ], 422);
                }

                $image = Photo::find($request->id);
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
