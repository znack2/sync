<?php
namespace Usedesk\SyncEngineIntegration\Commands;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Carbon\Carbon;

class SyncEngineImport extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'import:syncengine';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'SyncEngine import';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$imports = DB::table('sync_engine_import')->where('status',true)->get();

		foreach($imports as $import){
			$channel = CompanyEmailChannel::where('id',$import->channel_id)->first();
			$status = false;
			if($channel){
				if($channel->type == CompanyEmailChannel::TYPE_SYNC && $channel->sync_engine_id){
					$offset = 0;
					if($import->offset){
						$offset = $import->offset;
					}
					$emails = \UseDesk\SyncEngine\SyncEngineConnection::getEmailsFromSync( $channel->sync_engine_id,$offset);
					if($emails) {
						foreach ($emails as $attributes) {
							$syncEmail = new \UseDesk\SyncEngine\SyncEngineEmail($attributes,$channel);
							if($syncEmail){
								$result = $syncEmail->saveEmail();
							}
							$offset++;
						}
					}
						//---------------end foreach------//
						$count = count($emails);
						if (!$count || $count < 100) {
							$status = false;
						} else {
							$status = true;
						}

						DB::table('sync_engine_import')->where('id', $import->id)->update([
								'offset' => $offset,
								'status' => $status
						]);

				}


			}
		}

    }


	public function createTicketFromSync($channel,$attributes,$is_inbound = 0){
		try {
			$message = "";
			if (isset($attributes['body'])) {
				$message = $attributes['body'];
			}
			$subject = "";
			if (isset($attributes['subject'])) {
				$subject = $attributes['subject'];
			}
			$id = "";
			if (isset($attributes['id'])) {
				$id = $attributes['id'];
			}
			$from = ['name' => "", 'email' => ""];
			if (isset($attributes['from']) && isset($attributes['from'][0])) {
				$from['email'] = $attributes['from'][0]['email'];
				$from['name'] = $attributes['from'][0]['name'];
			}
			$account_id = 0;
			if (isset($attributes['account_id'])) {
				$account_id = $attributes['account_id'];
			}
			$thread_id = 0;
			if (isset($attributes['thread_id'])) {
				$thread_id = $attributes['thread_id'];
			}
			$date = Carbon::now();
			if (isset($attributes['date'])) {
				$date = Carbon::createFromTimestamp($attributes['date']);
			}

			$sync_comment = DB::table('sync_engine_ticket_comments')->where('sync_engine_id', $id)->first();
			if ($sync_comment) {
				return false;
			} else {
				$is_outgoing = false;
				if ($from['email'] == $channel->imap_username) {
					$is_outgoing = true;
				}

				if (!$is_outgoing) {
					$company_id = $channel->company_id;
					$client = Client::select('clients.id')
							->join('client_emails', 'client_emails.client_id', '=', 'clients.id')
							->where('clients.company_id', '=', $company_id)
							->where('client_emails.email', $from['email'])
							->first();

					//создание клиента если его нет
					if (!$client) {

						$client = Client::create(['name' => (empty($from['email'])) ? $from['email'] : $from['name'], 'company_id' => $company_id]);

						ClientEmail::create(['email' => $from['email'], 'client_id' => $client->id]);

						if (CompanyIntegration::boolCheck(Integration::TYPE_FULLCONTACT, $client->company_id)) {

							\UseDesk\Fullcontact\Fullcontact::socialsByEmail($client->id);

						}

					}
				}
				$thread = DB::table('sync_engine_tickets')->where('thread_id', $thread_id)->first();
				$ticket_id = 0;
				if ($thread) {
					$ticket_id = $thread->ticket_id;
				}

				if (!$ticket_id) {
					$ticket = new Ticket(['channel' => Ticket::CHANNEL_EMAIL]);
					$ticket->fill(['email_channel_id' => $channel->id, 'subject' => $subject]);
					if (!$is_outgoing && $client->id) {
						$ticket->client_id = $client->id;
					}
					$ticket->status_id = TicketStatus::getByKey(TicketStatus::SYSTEM_NEW)->id;
					$ticket->priority = Ticket::PRIORITY_MEDIUM;
					$ticket->type = Ticket::TYPE_QUESTION;
					$ticket->email_channel_subject = $subject;
					$ticket->email_channel_email = $from['email'];
					$ticket->company_id = $company_id;
					$ticket->setStatusUpdatedAt($date);
					$ticket->last_updated_at = $date;
					$ticket->published_at = $date;
					$ticket->additional_id = "sync";
					$ticket->save();
					$ticket_id = $ticket->id;
					DB::table('sync_engine_tickets')->insert([
							'ticket_id' => $ticket_id,
							'thread_id' => $thread_id
					]);
				}

				$query = [
						'type' => 'public',
						'message' => $message,
						'ticket_id' => $ticket_id,
						'published_at' => $date,
				];
				if (!$is_outgoing && isset($client)) {
					$query['from'] = "client";
					$query['client_id'] = $client->id;
				} else {
					$user = User::where('company_id', $channel->company_id)->first();
					$query['from'] = "user";
					$query['user_id'] = $user->id;
				}
				$ticketComment = new TicketComment($query);
				$ticketComment->save();
				DB::table('sync_engine_ticket_comments')->insert([
						'ticket_id' => $ticket_id,
						'comment_id' => $ticketComment->id,
						'sync_engine_id' => $id
				]);

				if(isset($attributes['to']) && count($attributes['to'])>1){
					foreach($attributes['to'] as $item){
						if(isset($item[1]) && $channel->imap_username !== $item[1]){
							TicketCommentCopyEmail::saveEmailCopy($item[1], TicketCommentCopyEmail::TYPE_CC, $ticketComment->id);
						}
					}
				}
				if(isset($attributes['cc']) && count($attributes['cc'])>1){
					foreach($attributes['cc'] as $item){
						if(isset($item[1]) && $channel->imap_username !== $item[1]){
							TicketCommentCopyEmail::saveEmailCopy($item[1], TicketCommentCopyEmail::TYPE_CC, $ticketComment->id);
						}
					}
				}
				if(isset($attributes['bcc']) && count($attributes['bcc'])>1){
					foreach($attributes['bcc'] as $item){
						if(isset($item[1]) && $channel->imap_username !== $item[1]){
							TicketCommentCopyEmail::saveEmailCopy($item[1], TicketCommentCopyEmail::TYPE_BCC, $ticketComment->id);
						}
					}
				}
				if (isset($attributes['files'])) {
					$files = $attributes['files'];
					foreach ($files as $file) {
						if (isset($file['id'])) {
							$file_id = $file['id'];
							$url = 'http://' . $account_id . '@188.93.209.204:15555/files/' . $file_id . '/download';
							DB::table('ticket_comment_files')->insert([
									'ticket_comment_id' => $ticketComment->id,
									'file' => $url
							]);
						}
					}

				}
			}
		}
		catch(Exception $e){
			Log::alert($e);
			return false;
		}
	}

}
