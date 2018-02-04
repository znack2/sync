<?php
Route::get('/test', function () {
   echo "test ok";
});
Route::group(['namespace' => 'Usedesk\SyncEngineIntegration\Controllers'], function()
{
   Route::any('/v1/syncEngine', ['uses' => 'SyncEngineController@saveFromSyncEngine']);
   //TODO: Sync Engine
   Route::post('/v1/syncEngine/createChannel', ['uses' => 'SyncEngineController@createChannel', 'as' => 'syncengine.create.channel']);
//   Route::get('/settings/channels/sync/create/', ['uses' => 'SyncEngineController@getEditSyncEngine', 'as' => 'user.company_email_channels.get_create_sync_engine']);
//   Route::post('/settings/channels/sync/create/', ['uses' => 'SyncEngineController@postEditSyncEngine', 'as' => 'user.company_email_channels.post_create_sync_engine']);
//   Route::get('/settings/channels/sync/edit/{id}', ['uses' => 'SyncEngineController@getEditSyncEngine', 'as' => 'user.company_email_channels.get_edit_sync_engine']);
//   Route::post('/settings/channels/sync/edit/{id}', ['uses' => 'SyncEngineController@postEditSyncEngine', 'as' => 'user.company_email_channels.post_edit_sync_engine']);

});