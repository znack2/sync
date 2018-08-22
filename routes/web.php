<?php declare(strict_types=1);

Route::group([
    'prefix' => config('app.version').'/syncEngine'
    // 'middleware' => 'checkToken'
], function () {

	Route::post('/syncEngine', 						
    	['uses' => 'SyncController@syncEngine', 							
    	 'as' 	=> 'sync.syncEngine'
	]);
});




