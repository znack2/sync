<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SyncEngineTicketComments extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sync_engine_ticket_comments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('ticket_id')->unsigned()->nullable();
//			$table->foreign('ticket_id', 'sync_engine_ticket_comments_ticket_id_foreign')->references('id')->on('tickets')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->integer('comment_id')->unsigned()->nullable();
//			$table->foreign('comment_id', 'sync_engine_comment_ticket_comment_id_foreign')->references('id')->on('ticket_comments')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->string('sync_engine_id')->nullable();

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sync_engine_ticket_comments');
	}

}
