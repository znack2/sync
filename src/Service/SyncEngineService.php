<?php declare(strict_types=1);

namespace Usedesk\SyncIntegration\Service;

class SyncEngineService 
{
    private $url = env('SYC_ENGINE_HOST', 'localhost') . ':5555';

     private $host;
    private $port;
    private $username;
    private $name;
    private $password;
    private $encrypted;
    private $is_unencrypted;

    /**
     */
    function __construct(array $imap, array $smtp, bool $reauth=0) {
        $this->imap_host = $imap['host'];
        $this->imap_port = $imap['port'];
        $this->imap_username = $imap['username'];
        $this->imap_password = $imap['password'];
        $this->imap_name = $this->imap_username;
        $this->smtp_host = $smtp['host'];
        $this->smtp_port = $smtp['port'];
        $this->smtp_username = $smtp['username'];
        $this->smtp_password = $smtp['password'];
        $this->smtp_name = $this->smtp_username;
        $this->reauth = $reauth;
    }
    
    /**
     */
    public function createConnection() 
    {
            $path = 'http://10.0.2.102:5555/connect/authorize';
            //curl -X POST http://5.101.77.14:15555/connect/authorize -d '{"name":"Test2","email_address": "sourinjir@mail.ru","password":"asdasd213123das","provider":"imap",
            // "settings":{"imap_host":"imap.mail.ru","imap_port":993,"imap_username":"sourinjir@mail.ru",
            //"imap_password":"dancemacabre","smtp_host":"smtp.mail.ru"
            //"smtp_port":465,"smtp_username":"sourinjir@mail.ru",
            //"smtp_password":"asdasd213123das","ssl_required":true}}'

            $data = [
                "name"=>$this->imap_name,
                "email_address"=>$this->imap_username,
                "password"=>$this->imap_password,
                "provider"=>"imap",
                "settings"=>[
                    "imap_host"=>$this->imap_host,
                    "imap_port"=>$this->imap_port,
                    "imap_username"=>$this->imap_username,
                    "imap_password"=>$this->imap_password,
                    "smtp_username"=>$this->smtp_username,
                    "smtp_host"=>$this->smtp_host,
                    "smtp_port"=>$this->smtp_port,
                    "smtp_password"=>$this->smtp_password,
                    "ssl_required"=>true,
                ],
                //"reauth"=> true
            ];
            if($this->reauth){
                $data['reauth']=true;
            }


           //  $connect = curl_init(); // initialize cURL connection
           //  $data = json_encode($data);
           //  curl_setopt($connect, CURLOPT_URL, $path);
           //  curl_setopt($connect,CURLOPT_HTTPPROXYTUNNEL, 1);
           //  //curl_setopt($connect, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
           //  curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
           //  curl_setopt($connect, CURLOPT_CUSTOMREQUEST, 'POST');
           //  curl_setopt($connect, CURLOPT_TIMEOUT, 10);
           //  curl_setopt($connect, CURLOPT_POST, true);
           // // curl_setopt($connect, CURLOPT_SSL_VERIFYPEER, false);
           //  curl_setopt($connect, CURLOPT_POSTFIELDS, $data);
           //  curl_setopt($connect, CURLOPT_HTTPHEADER, array(
           //          'Content-Type: application/json',
           //          'Content-Length: ' . strlen($data))
           //  );
           //  $result = curl_exec($connect);
           //  return json_decode($result,true);
    }

    /**
     * @param array|string $from
     * @param array|string $replyTo
     */
    public static function sendMessage($from, $replyTo, string $to, string $subject, string $body, array $files = [], $cc = null, $bcc = null, $id, $sync_message=0): bool 
    {
          $from_key = array_keys($from);
          $from = $from_key[0];
          $boundary = "XXXXboundary text";
          $headers = 'From: '.$from.' To: '.$to.'';
          if($cc){
            $headers.='Cc: '.$cc.'';
          }
          if($bcc){
            $headers.='Bcc: '.$bcc.'';
          }
          $headers.='Reply-To: '.$from.'Subject: '.$subject.' MIME-Version: 1.0 References: <'.$sync_message.'>
          Content-Type: multipart/mixed;boundary="'.$boundary.'"--'. $boundary.'
          Content-Type: text/html; charset=UTF-8'.$body.'';

          $mime = $headers;

          foreach ($files as $file) {
            if (!preg_match('/(.*?)_(.*?)$/',basename($file), $name)) 
                continue;
                $type = mime_content_type($file);
                $name = $name[2];
                $content = base64_encode(file_get_contents($file));
                $mime.='--'. $boundary.'
                Content-Type: '.$type.'; name="'.$name.'"
                Content-Transfer-Encoding: base64
                Content-Disposition: attachment; filename="'.$name.'"'.$content.'';
          }

          $mime .='--'. $boundary.'--';
          // $mime = rtrim(strtr(base64_encode($headers.$body), '+/', '-_'), '=');

          $url = 'http://' . $id . '@10.0.2.102:5555/send';

          // $connect = curl_init();
          // curl_setopt($connect, CURLOPT_URL, $url);
          // //curl_setopt($connect, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
          // curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
          // curl_setopt($connect, CURLOPT_CUSTOMREQUEST, 'POST');
          // curl_setopt($connect, CURLOPT_TIMEOUT, 20);
          // curl_setopt($connect, CURLOPT_POST, true);
          // curl_setopt($connect, CURLOPT_POSTFIELDS, $mime);
          // curl_setopt($connect, CURLOPT_HTTPHEADER, array('Content-Type: message/rfc822'));
          // $result = curl_exec($connect);
          // $error = curl_error ($connect);

          if($result){
              $res = json_decode($result,true);
              if(isset($res['api_error'])){
                  return false;
              }
              return $res;
          }
          return $result;
    }

    public function createAccount(string $email, string $password)
    {
        $params = [
            'email'     =>$email,
            'password'. =>$password
        ];

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json' . PHP_EOL,
                'content' => json_encode($params),
                'ignore_errors' => true
            ],
        ]);

        $path = 'http://' . $this->url . '/connect/authorize';

        $response = file_get_contents($path, false, $context);

        return json_decode($response, true);
    }

    /**
     */
    public static function getEmailsFromSync($id,$offset)
    {
        $url = 'http://'. $id .'@10.0.2.102:5555/messages?offset=' . $offset;

        // $connect = curl_init();
        // curl_setopt($connect, CURLOPT_URL, $url);
        // curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($connect, CURLOPT_TIMEOUT, 20);
        // $result = curl_exec($connect);
        // $error = curl_error ($connect);
        // $result = json_decode($result, true);
        return $result;
    }
}