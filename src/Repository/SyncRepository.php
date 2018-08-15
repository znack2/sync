<?php declare(strict_types=1);

namespace Usedesk\SyncIntegration\Repository;

use Usedesk\SyncIntegration\Models\VkChannel;

use App\Jobs\Client\{FindOrCreateClient};
use App\Jobs\Ticket\{FindOrCreateTicket};
use App\Jobs\Comment\{AddComment};

class SyncRepository
{
    /**
     */
    protected function createTicketFromSync($attributes): bool
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

        if (!empty($attributes['date'])) {
            $data['date'] = date("Y-m-d H:i:s", $attributes['date']['$date']);
        }

        $files = [];

        if (isset($attributes['files'])) {
            foreach ($attributes['files'] as $file) {
                if (isset($file['id'])) {
                    $file_id = $file['id'];
                    $file_data = [
                        'url' => 'http://' . ($data['account_id']) . '@' . $this->syncService .'/files/' . $file_id . '/download',
                        'filename' => $file['filename'],
                    ];

                    if (!empty($file['content_id'])) {
                        $file_data['content_id'] = $file['content_id'];
                    }

                    $files[] = $file_data;
                }
            }
        }

        $channel = $this->getChannel($attributes['to'][0]['email']);

        $name = $attributes['from'][0]['name'] ?? '';
        $name = explode(' ', $name);

        $params = [
            'email' => $attributes['from'][0]['email'],
            'client' => ['company_id' => $channel->company_id],
            'first_name' => !empty($name[0]) ? $name[0] : $attributes['from'][0]['email'],
            'last_name' => $name[1] ?? '',
        ];

        $client_id = dispatch_now(new FindOrCreateClient(
            $channel->company_id,
            'email',
            $params
        ));

        $owner = [ //owner
            'type' => 'App\Models\Client\Client', //owner
            'id' => $client_id,
        ];

        if (!$ticket_id = $this->findTicketById($attributes['body'], $attributes['thread_id'])) {
            $ticket = dispatch_now(new FindOrCreateTicket(
                $channel->company_id,
                $owner,
                $channel->id,
                'email', //channel type
                $attributes['thread_id'],
                $attributes['subject'],
                $data['date'] ?? date("Y-m-d H:i:s")
            ));

            $ticket_id = $ticket->id;
        }

        $requestData = [ 
            'user_type' => 'client',
            'message_type' => 'public',
            'message' => $attributes['body'],
            'subject' => $attributes['subject'],
            'is_html' => true,
            'has_file' => !empty($files),
            'all_data' => $data,

            'channel_type' => 'email',
            'channel_id' => $channel->id,
            'from_client' => [
                $client_id => $attributes['from'][0]['email']
            ]
        ];

        dispatch(new AddComment(
            $channel->company_id,
            $ticket_id,
            $user_id = 0,
            $owner,
            $requestData,
            $files
        ));
    }

    /**
     * @param $email
     * @return mixed
     * @throws \Exception
     */
    protected function getChannel(string $email)
    {
        $channel = DB::table('email_channels')
                        ->where('incoming_email', $email)
                        ->first();

        if (!$channel) {
            throw new \Exception('Channel not found - ' . $email);
        }

        return $channel;
    }

    protected function findTicketById($message, int $thread_id)
    {
        preg_match('/\<span ticket_id="([0-9]+)">/i', $message, $matches);

        if ($matches) 
        {
            DB::table('tickets')
                ->where('id', $matches[1])
                ->update(['thread_id' => $thread_id]);

            return $matches[1];
        }

        return null;
    }


    public function getAccounts()
    {
        return json_decode(file_get_contents($this->syncService . '/accounts'));
    }
}