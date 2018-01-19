<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSyncEngineIdToCompanyEmailChannelsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('company_email_channels', function(Blueprint $table)
		{
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
		Schema::table('company_email_channels', function(Blueprint $table)
		{
			$table->dropColumn('sync_engine_id');
		});
	}

}
