<?php declare(strict_types=1);

namespace Usedesk\SyncIntegration\Tests\Feature;

use Usedesk\SyncIntegration\Tests\TestCase;

// use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;

class ChannelTest extends TestCase
{
	// use DatabaseTransactions;

	/**
	 * @group postblock
	 * @group chat
	 */
	public function testChannelConnect()
	{
		$token = [];
		
		$response = $this->actingAs($this->user)
						 ->post(route('sync.create_channel',$token));

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