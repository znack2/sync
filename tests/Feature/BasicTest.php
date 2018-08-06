<?php

namespace Usedesk\SyncIntegration\Tests\Feature;

use Usedesk\SyncIntegration\Tests\TestCase;

// use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;

class BasicTest extends TestCase
{
	// use DatabaseTransactions;

	/**
	 * @group postblock
	 * @group chat
	 */
	public function testBasic()
	{
		$token = [];
		
		$response = $this->actingAs($this->user)
						 ->get('v1/syncEngine/test');

		$response->assertStatus(Response::HTTP_OK);
	} 
}