<?php
Route::get('/test', function () {
   echo "test ok";
});
Route::group(['namespace' => 'Usedesk\SyncEngineIntegration\Controllers'], function()
{
   Route::any('/v1/syncEngine', ['uses' => 'SyncEngineController@syncEngine']);
   Route::any('/v1/syncEngine/create-channel', ['uses' => 'SyncEngineController@createChannel']);
   Route::get('/v1/syncEngine/accounts', ['uses' => 'SyncEngineController@accounts']);
});