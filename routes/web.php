<?php declare(strict_types=1);

Route::group([
    'prefix' => 'api/'.config('app.version'). '/sync'
    // 'middleware' => 'checkToken'
], function () {

	Route::get('/test', function(){
		return 'module sync ok';
	});

	Route::post('/create', 						
    	['uses' => '\Freshplan\Sync\Controllers\SyncController@create',
    	// ['uses' => '\Usedesk\SyncIntegration\Controllers\TestController@create',
    	 'as' 	=> 'sync.create'
	]);

	/* ================== callback ================== */

	Route::get('callback',
	    // ['uses' => 'Channel\EmailChannelController@callback',
	     // 'as'   => 'channels.email.callback'
		['uses' => '\Freshplan\Sync\Controllers\CallbackController@callback',
	     'as'   => 'sync.callback'
	 ]);
});




