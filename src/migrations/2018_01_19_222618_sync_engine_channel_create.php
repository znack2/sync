<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SyncEngineChannelCreate extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sync_engine_channel', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('sync_engine_id')->nulable();
			$table->integer('company_id')->nulable();
			$table->integer('channel_id')->nulable();
			$table->string('provider')->default('imap');
			$table->string('imap_host')->nulable();
			$table->string('imap_port')->nulable();
			$table->string('imap_username')->nulable();
			$table->string('imap_password')->nulable();
			$table->string('smtp_host')->nulable();
			$table->string('smtp_port')->nulable();
			$table->string('smtp_username')->nulable();
			$table->string('smtp_password')->nulable();
			$table->boolean('imap_ssl')->default(true);
			$table->boolean('reauth')->default(true);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sync_engine_channel');
	}

}
