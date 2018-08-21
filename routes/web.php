<?php declare(strict_types=1);

Route::group([
    'prefix' => config('app.version').'/syncEngine'
    // 'middleware' => 'checkToken'
], function () {

	Route::get('/test', function () {
		echo "test ok";						
    });

	Route::post('/syncEngine', 						
    	['uses' => 'SyncController@syncEngine', 							
    	 'as' 	=> 'sync.syncEngine'
	]);

	// Route::post('/create-channel', 						
 //    	['uses' => 'SyncController@createChannel', 							
 //    	 'as' 	=> 'sync.createChannel'
	// ]);

	// Route::delete('/v1/syncEngine/delete-channel', 
	// 	['uses' => 'SyncController@deleteChannel',
	// 	 'as' 	=> 'sync.deleteChannel'
	// ]);

	// Route::get('/accounts', 						
 //    	['uses' => 'SyncController@accounts', 							
 //    	 'as' 	=> 'sync.accounts'
	// ]);
});




