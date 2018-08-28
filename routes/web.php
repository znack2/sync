<?php declare(strict_types=1);

Route::group([
    'prefix' => 'api/'.config('app.version')
    // 'middleware' => 'checkToken'
], function () {

	Route::post('/create', 						
    	['uses' => 'SyncController@create', 							
    	 'as' 	=> 'sync.create'
	]);
});




