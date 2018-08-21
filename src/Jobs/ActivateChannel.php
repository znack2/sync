<?php declare(strict_types=1);

namespace Usedesk\SyncIntegration\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Usedesk\SyncIntegration\Service\SyncEngineService;

class ActivateChannel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	private $helper;

    protected $params;
    protected $data;

    public function __construct(array $params, array $data)
    {
        $this->params = $params;
        $this->data = $data;
        $this->helper = new SyncEngineService;
    }

    public function handle()
    {
		$params = [
            'imap_host'         => $request->input('params.imap.host'),
            'imap_port'         => $request->input('params.imap.port', 993),
            'imap_username'     => $request->input('params.imap.username',  $request->input('incoming_email')),
            'imap_password'     => $request->input('params.imap.password'),
            'smtp_host'         => $request->input('params.smtp.host'),
            'smtp_port'         => $request->input('params.smtp.port', 465),
            'smtp_username'     => $request->input('params.smtp.username',  $request->input('outgoing_email')),
            'smtp_password'     => $request->input('params.smtp.password'),
            //change to imap_encrypt
            //change to smtp_encrypt
            'ssl_required'      => $request->input('ssl_required', true),
            'auth_code'         => $request->input('auth_code', 'get'),
            // token
        ];

        $data = [
            'name'          => $this->data['name'],
            'email_address' => $this->data['incoming_email'],
            'reauth'        => true/false, //external or 
            'password'      => $this->params['imap.password'],
            'settings'      => $params,
            'auth_code'     => $this->data['auth_code'],
        ];

        if ($request->has('incoming_main_email')) {
            $request->merge(['incoming_email' => $request->input('incoming_main_email')]);
        }

        $reauth_done = false;

        if (!$result['success']) {
            if (isset($result['type']) and $result['type'] == 'api_error' and !empty($result['message']) and $result['message'] == 'Already have this account!') {
        }

        return $this->helper->checkAccount($params);
    }

}