<?php
Route::get('/test', function () {
   echo "test ok";
});
Route::group(['namespace' => 'Usedesk\SyncEngineIntegration\Controllers'], function()
{
   Route::any('/v1/syncEngine', ['uses' => 'SyncEngineController@saveFromSyncEngine']);
});