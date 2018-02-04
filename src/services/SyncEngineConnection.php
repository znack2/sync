<?php

namespace Usedesk\SyncEngineIntegration\Services;

class SyncEngineConnection {

    /**
     * @var string
     */
    private $host;
    /**
     * @var integer
     */
    private $port;
    /**
     * @var string
     */
    private $username;
    private $name;
    /**
     * @var string
     */
    private $password;
    private $encrypted;
    /**
     * @var bool
     */
    private $is_unencrypted;

    /**
     * @param string $host
     * @param integer $port
     * @param string $username
     * @param string $password
     * @param bool $is_unencrypted
     */
    function __construct($imap,$smtp,$reauth=0) {
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
     * @return string
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host) {
        $this->host = $host;
    }

    /**
     * @return int
     */
    public function getPort() {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort($port) {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * @return bool
     */
    public function isUnencrypted() {
        return $this->is_unencrypted;
    }

    /**
     * @param bool $is_unencrypted
     */
    public function setIsUnencrypted($is_unencrypted) {
        $this->is_unencrypted = $is_unencrypted;
    }

    /**
     * @return bool
     */
    public function check() {
    }
    public function createConnection() {
        try{
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
            $connect = curl_init(); // initialize cURL connection
            $data = json_encode($data);
            curl_setopt($connect, CURLOPT_URL, $path);
            curl_setopt($connect,CURLOPT_HTTPPROXYTUNNEL, 1);
            //curl_setopt($connect, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
            curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($connect, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($connect, CURLOPT_TIMEOUT, 10);
            curl_setopt($connect, CURLOPT_POST, true);
           // curl_setopt($connect, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($connect, CURLOPT_POSTFIELDS, $data);
            curl_setopt($connect, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data))
            );
            $result = curl_exec($connect);
            \Log::alert($result);
            return json_decode($result,true);
        }
        catch(\Exception $e){
            \Log::alert($e);
            return false;
        }
    }

    /**
     * @param array|string $from
     * @param array|string $replyTo
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param array $files
     * @return bool
     */
    public static function sendMessage($from, $replyTo, $to, $subject, $body, $files = [], $cc = null, $bcc = null, $id, $sync_message=0) {
      try {
          $from_key = array_keys($from);
          $from = $from_key[0];
          $boundary = "XXXXboundary text";
          $headers = 'From: '.$from.'
To: '.$to.'
';
if($cc){
$headers.='Cc: '.$cc.'
';
}
if($bcc){
$headers.='Bcc: '.$bcc.'
';
}
$headers.='Reply-To: '.$from.'
Subject: '.$subject.'
MIME-Version: 1.0
References: <'.$sync_message.'>
Content-Type: multipart/mixed;boundary="'.$boundary.'"

--'. $boundary.'
Content-Type: text/html; charset=UTF-8
'.$body.'
';
          $mime = $headers;
          foreach ($files as $file) {
              if (!preg_match('/(.*?)_(.*?)$/',basename($file), $name)) continue;
              $type = mime_content_type($file);
              $name = $name[2];
              $content = base64_encode(file_get_contents($file));
              $mime.='--'. $boundary.'
Content-Type: '.$type.'; name="'.$name.'"
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="'.$name.'"
'.$content.'
';
          }
          $mime .='
--'. $boundary.'--';
        // $mime = rtrim(strtr(base64_encode($headers.$body), '+/', '-_'), '=');

          $url = 'http://' . $id . '@10.0.2.102:5555/send';
          $connect = curl_init();
          curl_setopt($connect, CURLOPT_URL, $url);
          //curl_setopt($connect, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
          curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($connect, CURLOPT_CUSTOMREQUEST, 'POST');
          curl_setopt($connect, CURLOPT_TIMEOUT, 20);
          curl_setopt($connect, CURLOPT_POST, true);
          curl_setopt($connect, CURLOPT_POSTFIELDS, $mime);
          curl_setopt($connect, CURLOPT_HTTPHEADER, array('Content-Type: message/rfc822'));
          $result = curl_exec($connect);
          $error = curl_error ($connect);
          \Log::alert($error);
          \Log::alert($result);
          if($error){
              return false;
          }

          if($result){
              $res = json_decode($result,true);
              if(isset($res['api_error'])){
                  return false;
              }
              return $res;
          }
          return $result;
      }
      catch(\Exception $e){
          \Log::alert($e);
          return false;
      }

    }

    public static function getEmailsFromSync($id,$offset){
        try {
            $url = 'http://'. $id .'@10.0.2.102:5555/messages?offset=' . $offset;
            $connect = curl_init();
            curl_setopt($connect, CURLOPT_URL, $url);
            curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($connect, CURLOPT_TIMEOUT, 20);
            $result = curl_exec($connect);

            $error = curl_error ($connect);
            if($error){
                \Log::alert($error);
                return false;
            }
            $result = json_decode($result, true);
            return $result;
        }
        catch(\Exception $e){
            \Log::alert($e);
            return false;
        }
    }
}
