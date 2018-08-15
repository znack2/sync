<?php
Route::group(['namespace' => 'Usedesk\SyncIntegration\Controllers'], function()
{
   Route::any('/v1/syncEngine', ['uses' => 'SyncEngineController@syncEngine']);
   Route::any('/v1/syncEngine/create-channel', ['uses' => 'SyncEngineController@createChannel']);
   Route::any('/v1/syncEngine/delete-channel', ['uses' => 'SyncEngineController@deleteChannel']);
   Route::get('/v1/syncEngine/accounts', ['uses' => 'SyncEngineController@accounts']);
});