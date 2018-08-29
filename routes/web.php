<?php declare(strict_types=1);

Route::group([
    'prefix' => 'api/'.config('app.version'). '/sync'
    // 'middleware' => 'checkToken'
], function () {

	Route::post('/create', 						
    	['uses' => '\Usedesk\SyncIntegration\Controllers\SyncController@create',					
    	 'as' 	=> 'sync.create'
	]);

	Route::post('/callback', 						
    	['uses' => '\Usedesk\SyncIntegration\Controllers\SyncController@callback',       				
    	 'as' 	=> 'sync.callback'
	]);
});




