<?php declare(strict_types=1);

namespace Freshplan\Sync\Tests\Feature;

use Freshplan\Sync\Tests\TestCase;

// use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;

class CreateChannelTest extends TestCase
{
	// use DatabaseTransactions;

	/**
	 * @group postchannel
	 * @group channel
	 */
	public function testCreateChannelConnect()
	{
		// create method comtroller

		//get data
		//format data
		//addComment job
		//AddTicketContact pivot job 

		$response = $this->actingAs($this->user)
						 ->post(route('sync.create',$token));


		$response
            ->dump()   
            ->assertStatus(Response::HTTP_OK); 
	} 
}