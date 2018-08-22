<?php declare(strict_types=1);

namespace Usedesk\SyncIntegration\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Usedesk\SyncIntegration\Service\SyncEngineService;

class CreateChannel implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $helper;
    
    private $params;
    private $data;

    public function __construct(array $params = [],array $data = [])
    {
        $this->params = $params;
        $this->data = $data;
        $this->helper = new SyncEngineService;
    }

    public function handle(): JsonResponse
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

        $this->helper->createAccount($params);

        $response['message'] = 'success';// ->header('Content-Type', 'application/json');

        return $this->sendResponse($response);




           // if (isset($result['type']) and $result['type'] == 'oauth') {
           //      return $this->helper->sendResponse($result);
           //  }

            // if (isset($result['type']) and $result['type'] == 'oauth') {
            //         return $this->helper->sendResponse($result);
            //     }

            //     if (!$result['success']) {
            //         return $this->helper->sendError($result);
            //     }
            //     $reauth_done = true;
            // }
            // if (!$reauth_done) {
            //     return $this->helper->sendError($result);
            // }

        // if (!empty($result['type'])) {
        //     return [
        //         'success' => false,
        //         'type' => $result['type'],
        //         'message' => $result['message'],
        //     ];
        // }

        // if (!empty($result['oauth2_url'])) {
        //     return [
        //         'success' => false,
        //         'type' => 'oauth',
        //         'oauth2_url' => $result['oauth2_url'],
        //     ];
        // }

        // return [
        //     'success' => true,
        //     'type' => 'success',
        //     'data' => $result,
        // ];
    }

}





// if ($request->has('incoming_main_email')) {
//             $request->merge(['incoming_email' => $request->input('incoming_main_email')]);
//         }

        
//         $extra = [];

//         if (isset($requestData['quotes'])) {
//             $extra['quotes'] = $requestData['quotes'];
//             unset($requestData['quotes']);
//         }

//         if (isset($requestData['csi'])) {
//             $extra['csi'] = $requestData['csi'];
//             unset($requestData['csi']);
//         }

//         if ($extra) {
//             $requestData['extra_email'] = json_encode($extra);
//         }

//         if ($requestData['incoming_connection'] == 'external') {
//             $result = dispatch_now(new CreateChannel(
//                 $request->input('name'),
//                 $request->input('incoming_email'),
//                 $request->input('params.imap.password'),
//                 $syncEngineParams,
//                 false
//             ));

//             $reauth_done = false;
//             if (!$result['success']) {
//                 if (isset($result['type']) and $result['type'] == 'oauth') {
//                     return $this->helper->sendResponse($result);
//                 }

//                 if (isset($result['type']) and $result['type'] == 'api_error' and !empty($result['message']) and $result['message'] == 'Already have this account!') {
//                     $result = dispatch_now(new CreateChannel(
//                         $request->input('name'),
//                         $request->input('incoming_email'),
//                         $request->input('params.imap.password'),
//                         $syncEngineParams,
//                         true
//                     ));

//                     if (isset($result['type']) and $result['type'] == 'oauth') {
//                         return $this->helper->sendResponse($result);
//                     }

//                     if (!$result['success']) {
//                         return $this->helper->sendError($result);
//                     }
//                     $reauth_done = true;
//                 }
//                 if (!$reauth_done) {
//                     return $this->helper->sendError($result);
//                 }
//             }
//         }