<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\PhotoController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// API routes
Route::post("users/register", [ApiController::class, "register"]);
Route::post("users/createUser", [ApiController::class, "createUser"]);
Route::post("users/login", [ApiController::class, "login"]);
Route::get("users/list", [ApiController::class, "listUsers"]);
Route::get("users/find/byName", [ApiController::class, "findByName"]);
Route::get("users/find/byApiKey", [ApiController::class, "findByApiKey"]);
Route::get("users/listDeleted", [ApiController::class, "listDeletedUsers"]);
Route::put("users/softDelete/byApiKey", [ApiController::class, "softDeleteUser"]);
Route::put("users/softUndelete/byApiKey", [ApiController::class, "softUndeleteUser"]);

Route::post("photos/upload", [PhotoController::class, "upload"]);
Route::post("photos/multiUpload", [PhotoController::class, "multiUpload"]);
Route::get("photos/list", [PhotoController::class, "listPhotos"]);
Route::get("photos/listDeleted", [PhotoController::class, "listDeletedPhotos"]);
Route::get("photos/find/byFileName", [PhotoController::class, "findPhotoByFileName"]);
Route::get("photos/find/byId", [PhotoController::class, "findPhotoById"]);
Route::put("photos/softDelete", [PhotoController::class, "softDeletePhoto"]);
Route::put("photos/softUndelete", [PhotoController::class, "softUndeletePhoto"]);
Route::put("photos/update/byId", [PhotoController::class, "updatePhoto"]);



Route::group([
    "middleware" => ["auth:api"]
], function () {
    Route::get("users/profile", [ApiController::class, "profile"]);
    Route::get("users/refresh", [ApiController::class, "refreshToken"]);
    Route::get("users/logout", [ApiController::class, "logout"]);
});
