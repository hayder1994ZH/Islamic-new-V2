<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// Route::get('file/getFileData','FilesController@getFileData');
Route::get('client/my/company/get', 'CompaniesController@getMyCompanyClient'); // Login (Company)
Route::apiResource('downloads', 'DownloadController');//
Route::post('auth/google', 'UserController@googleLogin');
Route::post('auth/login', 'UserController@login');
Route::post('auth/login/api', 'UserController@loginAPi');
Route::post('auth/register', 'UserController@register');
Route::post('auth/register/api', 'UserController@registerAPI');
Route::post('auth/recovery/api', 'UserController@recoveryAPI');
Route::get('file/filter/{id}', 'FilesController@filter');
Route::get('file/getFromTemp', 'FilesController@getFromTemp');
Route::get('file/rates/{id}', 'FilesController@rates');
Route::get('download/{id}', 'FilesController@download');
Route::get('download/mobile/{recaptcha}/{id}', 'FilesController@downloadMobile');
Route::get('down/{key}', 'FilesController@down')->name('download');
Route::get('user/{id}', 'UserController@show');
Route::get('file/search', 'FilesController@index');
Route::get('file/views/{id}', 'FilesController@views');
Route::get('file/{id}', 'FilesController@show');
Route::get('category/list', 'CategoriesController@index');
Route::get('category/vocalist/list/{id}', 'CategoriesController@getVocalistByCategory');
Route::get('subcategory/list', 'SubcategoriesController@index');
Route::get('tag/list', 'TagController@index');
Route::get('slider/list', 'SliderController@index');
Route::get('slider/vocalist/list', 'SliderVocalistController@index');
Route::apiResource('order', 'OrderController');
Route::get('comments/file/{id}', 'CommentsController@commentsById');
Route::get('get/file', 'FilesController@index');
Route::get('get/file/views', 'FilesController@getSortByView');
Route::get('get/file/downloads', 'FilesController@getSortByDownload');
Route::get('get/file/ratings', 'FilesController@getSortByRating');
Route::get('ads', 'AdsController@getlist');
Route::get('ads/{id}', 'AdsController@show');
Route::post('ads', 'AdsController@store');
Route::put('ads/{id}', 'AdsController@update');
Route::delete('ads', 'AdsController@destroy');
Route::get('file/get/vocalist/{id}', 'FilesController@getByVocaId');//getByCategorysId
Route::get('file/get/vocalist/{id}/{category_id}', 'FilesController@getByVocaIdIdAndCategoryId');
Route::get('get/vocalist/category/{id}', 'FilesController@getByCategorysId');
Route::get('file/get/all/vocalist/{id}', 'FilesController@getAllByVocaId');
Route::get('file/get/collection/{id}', 'FilesController@getByCollectionId');
Route::get('vocalist/get', 'VocalistController@index');
Route::get('vocalist/get/{id}', 'VocalistController@show');
Route::get('collection/get', 'CollectionController@index');
Route::get('collection/get/{id}', 'CollectionController@show');
Route::get('social/get', 'SocialController@index');
Route::get('social/get/{id}', 'SocialController@show');
Route::get('social/get/all/files', 'SocialController@getAllSocialFiles');
Route::get('social/view', 'SocialController@getAllSocialViews');
Route::get('files', 'FilesController@index');
Route::get('version/clint/{version}', 'VersionController@showByVersion');//getAllSocialView
Route::get('get/file/category/{category_id}/{vocalist_id}', 'FilesController@getRandomFileByCategoryId');

Route::group(['middleware' => ['auth']], function (){
    Route::post('file/playlist', 'FilesController@playlist');
    Route::delete('file/playlist/delete/{id}', 'FilesController@deleteFromPlaylist');
    Route::get('file/playlist/get', 'FilesController@getMyPlaylist');
    Route::post('social/add', 'SocialController@store');
    Route::post('social/like', 'SocialController@like');
    Route::get('unaprove/file/social', 'SocialController@getUnApproveSocial');
    Route::get('me', 'UserController@me');
    Route::get('fileData/{id}', 'FilesController@geter');
    Route::get('get/report', 'UserController@getReport');
    Route::get('comment/reports/search', 'CommentReportsController@search');
    Route::get('file/reports/search', 'Files_reportController@search');
    Route::get('files/report/getAll', 'Files_reportController@index');
    Route::put('me/update/profile', 'UserController@updateProfile');
    Route::get('me/logout', 'UserController@logout');
    Route::get('file/reports/dashboard', 'FilesController@dashboardAdmin');
    Route::apiResource('file/reports', 'Files_reportController');
    Route::apiResource('comment/reports', 'CommentReportsController');
    Route::apiResource('user', 'UserController');
    Route::apiResource('slider/vocalist', 'SliderVocalistController');
    Route::apiResource('file', 'FilesController');
    Route::apiResource('social/comment', 'SocialCommentController');
    Route::patch('file/approve/{id}', 'FilesController@approve');
    Route::get('social/approve/{id}', 'SocialController@approve');
    Route::get('file/getById/{id}', 'FilesController@getFilebyId');
    Route::apiResource('category', 'CategoriesController');
    Route::apiResource('vocalist', 'VocalistController');
    Route::apiResource('version', 'VersionController');
    Route::apiResource('collection', 'CollectionController');
    Route::apiResource('social', 'SocialController');
    Route::apiResource('comment', 'CommentsController');
    Route::post('like', 'LikesController@like');
    Route::post('file/store', 'FilesController@store');
    Route::post('file/upload/{id}', 'FilesController@upload');
    Route::delete('file/object/delete/{id}', 'FilesController@deleteFileObject');
    Route::post('rate', 'RatingController@add'); // Login (Anyone)
    Route::post('rate2', 'RatingController@add2'); // Login (Anyone)
    Route::delete('downloads/delete/all', 'DownloadController@destroyAll'); // Login (Anyone)
    Route::apiResource('tag', 'TagController'); // Login (Admnin)
    Route::post('like', 'LikesController@like'); // Login (Admnin, Operation, Company, User)
    Route::post('favorite/add', 'FavoriteController@store'); // Login (Admnin, Operation, Company, User)
    Route::get('favorite/my', 'FavoriteController@myFavorite'); // Login (Admnin, Operation, Company, User)
    Route::delete('favorite/delete', 'FavoriteController@destroy'); // Login (Admnin, Operation, Company, User)
    Route::apiResource('company', 'CompaniesController'); // Login (Admnin)
    Route::get('my/company/get', 'CompaniesController@getMyCompany'); // Login (Company)
    Route::put('my/company/update', 'CompaniesController@updateMyCompany'); // Login (Company)
    Route::apiResource('role', 'RolesController')->middleware('role:Admin');
    Route::get('order/status/{id}', 'OrderController@updateStatus');
    Route::apiResource('slider', 'SliderController')->middleware('role:Admin');
    Route::get('count/download', 'DownloadController@count'); // Login (Company)
});

Route::get('auth/google', 'GoogleController@redirectToGoogle');
Route::get('auth/google/callback', 'GoogleController@handleGoogleCallback');
