<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\PhotoController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});





Route::group(['prefix' => 'users'], function () {
    Route::post("register", [ApiController::class, "register"]);
    Route::post("login", [ApiController::class, "login"]);
    Route::post("createUser", [ApiController::class, "createUser"]);
    Route::get("list", [ApiController::class, "listUsers"]);
    Route::get("find/byName", [ApiController::class, "findByName"]);
    Route::get("find/byApiKey", [ApiController::class, "findByApiKey"]);
    Route::get("listDeleted", [ApiController::class, "listDeletedUsers"]);
    Route::put("softDelete/byApiKey", [ApiController::class, "softDeleteUser"]);
    Route::put("softUndelete/byApiKey", [ApiController::class, "softUndeleteUser"]);
    Route::get("profile", [ApiController::class, "profile"]);
    Route::get("refresh", [ApiController::class, "refreshToken"]);
    Route::get("logout", [ApiController::class, "logout"]);
});

Route::group(['prefix' => 'photos'], function () {
    Route::post("upload", [PhotoController::class, "upload"]);
    Route::post("multiUpload", [PhotoController::class, "multiUpload"]);
    Route::get("list", [PhotoController::class, "listPhotos"]);
    Route::get("listDeleted", [PhotoController::class, "listDeletedPhotos"]);
    Route::get("find/byFileName", [PhotoController::class, "findPhotoByFileName"]);
    Route::get("find/byId", [PhotoController::class, "findPhotoById"]);
    Route::put("softDelete", [PhotoController::class, "softDeletePhoto"]);
    Route::put("softUndelete", [PhotoController::class, "softUndeletePhoto"]);
    Route::put("update/byId", [PhotoController::class, "updatePhoto"]);
});


Route::group(['prefix' => 'users'], function () {
    Route::group(["middleware" => ["auth:api"]], function () {
        Route::get("profile", [ApiController::class, "profile"]);
        Route::get("refresh", [ApiController::class, "refreshToken"]);
        Route::get("logout", [ApiController::class, "logout"]);
    });
});


/*



Route::group(['prefix' => 'users'], function () {
        Route::post("register", [ApiController::class, "register"]); // Register a new user
        Route::post("login", [ApiController::class, "login"]); // Login user
        Route::post("create", [ApiController::class, "createUser"]); // Create a new user
        Route::get("list", [ApiController::class, "listUsers"]); // List all users
        Route::get("search", [ApiController::class, "findByName"]); // Search users by name
        Route::get("{apiKey}", [ApiController::class, "findByApiKey"]); // Find user by API key
        Route::get("list/deleted", [ApiController::class, "listDeletedUsers"]); // List deleted users
        Route::put("{apiKey}/softDelete", [ApiController::class, "softDeleteUser"]); // Soft delete user by API key
        Route::put("{apiKey}/restore", [ApiController::class, "softUndeleteUser"]); // Restore soft deleted user by API key
        Route::get("profile", [ApiController::class, "profile"]); // Get user profile
        Route::get("refreshToken", [ApiController::class, "refreshToken"]); // Refresh user token
        Route::get("logout", [ApiController::class, "logout"]); // Logout user
   
});


Route::group(['prefix' => 'photos'], function () {
        Route::post("", [PhotoController::class, "upload"]); // Upload a photo
        Route::post("multiUpload", [PhotoController::class, "multiUpload"]); // Upload multiple photos
        Route::get("", [PhotoController::class, "listPhotos"]); // List all photos
        Route::get("deleted", [PhotoController::class, "listDeletedPhotos"]); // List deleted photos
        Route::get("search", [PhotoController::class, "findPhotoByFileName"]); // Search photo by file name
        Route::get("{id}", [PhotoController::class, "findPhotoById"]); // Get photo by ID
        Route::put("{id}/softDelete", [PhotoController::class, "softDeletePhoto"]); // Soft delete photo by ID
        Route::put("{id}/restore", [PhotoController::class, "softUndeletePhoto"]); // Restore soft deleted photo by ID
        Route::put("{id}/update", [PhotoController::class, "updatePhoto"]); // Update photo by ID
});
*/