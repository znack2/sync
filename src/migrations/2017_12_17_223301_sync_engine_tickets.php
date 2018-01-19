<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SyncEngineTickets extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sync_engine_tickets', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('ticket_id')->unsigned()->nullable();
			$table->foreign('ticket_id', 'sync_engine_ticket_ticket_id_foreign')->references('id')->on('tickets')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->string('thread_id')->nullable();

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sync_engine_tickets');
	}

}
