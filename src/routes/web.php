<?php
$secureRoutes = function() {
    Route::any('/v1/syncEngine', ['uses' => 'SyncEngineController@syncEngine']);
};