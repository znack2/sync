<?php

namespace Usedesk\Sync\Tests\Feature;

use Usedesk\Sync\Tests\TestCase;

// use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;

class AccountTest extends TestCase
{
	// use DatabaseTransactions;

	/**
	 * @group postblock
	 * @group chat
	 */
	public function testAccountConnect()
	{
		$token = [];
		
		$response = $this->actingAs($this->user)
						 ->get(route('sync.accounts',$token));

		$response
            // ->dump()   
            ->assertStatus(Response::HTTP_OK)   
            ->assertJson([
            	"success" => true,
            	"data" => [
            		// $blockData,
            		"message" => "chat connected successfully."
            	],
                "statusCode" => Response::HTTP_OK,
			    "version" => config('app.version'),
			    "author_url" => config('app.url'),
			    "user_id" => $this->user->id
            ]);
	} 
}