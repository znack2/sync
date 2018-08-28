<?php

namespace Usedesk\SyncIntegration\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

use App\Jobs\Client\{FindOrCreateClient};
use App\Jobs\Ticket\{FindOrCreateTicket};//CheckDouble
use App\Jobs\Comment\{AddComment};

class SyncController
{
	 /**
     */
    public function create(Request $request): string
    {
        $requestData = $request->all();

        foreach($requestData as $item)
        {
            $data = prepare($item['attributes']);

            $data['date'] = $this->setDate($item['attributes']['date']);

            $channel = $this->setChannel($item['attributes']['to'][0]['email']);
            
            $client = $this->setClient($item['attributes']['from'],$channel->company_id);

            $files = $this->setFiles($item['attributes']['files']);

            $ticket = $this->setTicket(
                $company_id,
                $thread_id,
                $channel_id,
                $item['attributes'],
                $owner
            );

            $this->sendComment(
                $company_id,
                $ticket,
                $client,
                $channel,
                $from,
                $data,
                $files
            );

            dispatch(new AddTicketContact(
                 $company_id,
                 $ticket,
                 $comment_id,
                 $client
            ));
        }

        return 'ok';
    }


    // if (dispatch_now(new CheckDouble($channel->company_id, $client_id, $data['subject'], $data['date']))) {
    //     return false;
    // }

    private function prepare(array $attributes)
    {
        $data = [];

        $need = ['body', 'from', 'account_id', 'thread_id', 'snippet', 'id', 'subject'];

        foreach ($need as $key) {
            $row = $attributes[$key] ?? null;

            if ($row === null) {
                continue;
            }

            if (is_array($key) or is_object($key)) {
                $data[$key] = json_encode($row);
                continue;
            }

            $data[$key] = $row;
        }

        return $data;
    }

    private function setDate(array $date)
    {
        if (!empty($date)) {
                $data = date("Y-m-d H:i:s", $date['$date']);
            } else {
                $data = date("Y-m-d H:i:s");
            }
        return $data;
    }

    private function setChannel(string $email): array
    {
        $channel = DB::table('email_channels')->where('incoming_email', $email)->first();

        $channel = [
            'channel'=>[
                'channel_type' => 'email',
                'channel_id'   => $channel->id,
            ],
        ];

        return $channel;
    }

    private function setFiles(array $files): array
    {
        $result = [];

            if (isset($files)) {
                foreach ($files as $file) {
                    if (isset($file['id'])) {
                        $file_id = $file['id'];

                        $url = env('SYC_ENGINE_HOST', 'localhost') . ':5555';

                        $file_data = [
                            'url' => 'http://' . ($data['account_id']) . '@' . $url .'/files/' . $file_id . '/download',
                            'filename' => $file['filename'],
                        ];

                        if (!empty($file['content_id'])) {
                            $file_data['content_id'] = $file['content_id'];
                        }

                        $result[] = $file_data;
                    }
                }
            }
        return $result;
    }

    private function setClient(array $from,int $company_id): array
    {
        $name = $from[0]['name'] ?? '';
        $name = explode(' ', $name);

        $params = [
            'email'      => $from[0]['email'],
            'client'     => ['company_id' => $company_id],
            'first_name' => !empty($name[0]) ? $name[0] : $from[0]['email'],
            'last_name'  => $name[1] ?? '',
        ];

        $client_id = dispatch_now(new FindOrCreateClient(
            $company_id,
            'email',
            $params
        ));

        $client = [ 
            $client_id => $from[0]['email']
        ];

        return $client;
    }

    private function sendComment(int $company_id, $ticket, array $client, array $channel, array $data, array $files = []): int
    {
        $ticketData = [ 
            'title'                 => $data['subject'],
            'id'                    => $ticket->id;
            'current_status_id'     => $ticket->status_id;
            'is_rated'              => $ticket->is_rated;
            'last_updated_at'       => $ticket->last_updated_at;
            'lastClientMessage'     => $data['body_quotes'],
            'is_html'               => true,
            'has_file'              => !empty($files),
        ];

        $commentData = [
            'message'=>[
                'type' => 'public',
                'body' => $data['body'],
                'is_first' => '',
            ],
        ];

        dispatch(new AddComment(
            $company_id,
            $user_id = null,
            $owner = null,
            $channel,
            $client,
            $commentData,
            $ticketData,
            $files
        ));
    }


    private function setTicket(int $company_id, int $thread_id, int $channel_id, array $attributes,array $owner)
    {
        preg_match('/\<span ticket_id="([0-9]+)">/i', $attributes['body'], $matches);

        //TODO: remove repository
        // if ($matches) {
        //     DB::table('tickets')->where('id', $matches[1])->update(['thread_id' => $attributes['thread_id']]);
        //     return $matches[1];
        // }

        $ticket = dispatch_now(new FindOrCreateTicket(
            $company_id,
            $owner,
            $channel_id,
            $channel_type = 'email',
            $attributes['thread_id'],
            $attributes['subject'],
            $data['date']
        ));

        return $ticket;
    }

}