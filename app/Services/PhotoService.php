<?php

namespace App\Services;

use App\Models\Photo;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Illuminate\Support\Facades\Gate;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
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
}
