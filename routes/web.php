<?php declare(strict_types=1);

Route::group([
    'prefix' => 'api/'.config('app.version'). '/sync'
    // 'middleware' => 'checkToken'
], function () {

	Route::post('/create', 						
    	['uses' => 'SyncController@create', 							
    	 'as' 	=> 'sync.create'
	]);

	Route::post('/callback', 						
    	['uses' => 'SyncController@callback', 							
    	 'as' 	=> 'sync.callback'
	]);
});




