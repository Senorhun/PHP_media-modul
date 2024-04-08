<?php

namespace App\Services;

use App\Models\Photo;

use Illuminate\Support\Facades\Gate;

use Illuminate\Support\Str;

class PhotoService
{

    public function upload($request, $user)
    {


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
    }

    public function multiUpload($request, $user)
    {
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
    }

    public function listPhotos()
    {
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
    }

    public function listDeletedPhotos()
    {
        // Logic for listing deleted photos
    }

    public function findPhotoByFileName($fileName)
    {
        // Logic for finding photo by file name
    }

    public function findPhotoById($photoId)
    {
        // Logic for finding photo by ID
    }

    public function updatePhoto($request, $photoId)
    {
        // Logic for updating photo
    }

    public function softDeletePhoto($photoId)
    {
        // Logic for soft deleting photo
    }

    public function softUndeletePhoto($photoId)
    {
        // Logic for soft undeleting photo
    }
}
