<?php
namespace Osynapsy\Mailer\Client;

use Osynapsy\Mailer\Email\Message;

/**
 * Description of Connection
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class SmtpClient implements ClientInterface
{
    const NEWLINE = "\r\n";

    private string $server;
    private int $port;
    private ?string $username;
    private ?string $password;
    private ?string $secure;

    private $conn;
    private $response = [];
    private string $localhost = 'localhost';
    private int $timeout = 60;
    private array $contextOptions = [];
    private string $currentResponse;
    private bool $isLogin = false;

    public function __construct(string $server, int $port, ?string $username = null, ?string $password = null, ?string $secure = null)
    {
        $this->server = $server;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->secure = $secure ? strtolower($secure) : null;
        if ($this->secure === 'ssl') {
            $this->server = 'ssl://' . $server;
        }
    }

    // -------------------------------------------------
    // ✅ invio singolo tramite Message
    public function sendMessage(Message $msg)
    {
        if (!$this->ensureConnection()) {
            return;
        }
        if (!$this->isLogin) {
            throw new \Exception("Not authenticated on SMTP server");
        }

        $from = $msg->getFrom();
        $toList = array_merge($msg->getTo(), $msg->getCc(), $msg->getBcc());

        // Mittente
        if (substr($this->putRow('MAIL FROM: <' . $this->getMailAddr($from) . '>'),0,3) != '250') {
            throw new \Exception("MAIL FROM failed: " . $this->currentResponse);
        }

        // Destinatari
        foreach ($toList as $recipient) {
            if (substr($this->putRow('RCPT TO: <' . $this->getMailAddr($recipient) . '>'),0,3) != '250' &&
                substr($this->currentResponse,0,3) != '251') { // 251 = recipient forwarding
                throw new \Exception("RCPT TO failed for $recipient: " . $this->currentResponse);
            }
        }

        // Inizio DATA
        if (substr($this->putRow('DATA'),0,3) != '354') {
            throw new \Exception("Server not ready for data: " . $this->currentResponse);
        }

        // Corpo del messaggio
        $emailRaw = strval($msg);

        // Terminazione messaggio con CRLF + punto + CRLF
        fputs($this->conn, rtrim($emailRaw, "\r\n") . self::NEWLINE . '.' . self::NEWLINE);
        $this->currentResponse = $this->getServerResponse();

        if (substr($this->currentResponse,0,3) != '250') {
            throw new \Exception("Mail not accepted by server: " . $this->currentResponse);
        }
    }

    private function ensureConnection(): bool
    {
        if (in_array($this->secure, ['tls', 'ssl'])) {
            $this->verifyCertificate(false);
        }
        if ($this->conn === null) {
            $this->connect();
            $this->auth();
        }
        return true;
    }

    public function verifyCertificate(bool $verify)
    {
        if (!isset($this->contextOptions['ssl'])) {
            $this->contextOptions['ssl'] = [];
        }
        $this->contextOptions['ssl']['verify_peer'] = $verify;
        $this->contextOptions['ssl']['verify_peer_name'] = $verify;
        $this->contextOptions['ssl']['allow_self_signed'] = !$verify;
    }

    private function connect()
    {
        $streamContext = stream_context_create($this->contextOptions);
        $this->conn = stream_socket_client(
            $this->server.':'.$this->port,
            $errno,
            $errstr,
            $this->timeout,
            STREAM_CLIENT_CONNECT,
            $streamContext
        );

        if (!$this->conn) {
            throw new \Exception("Connection failed: $errno $errstr");
        }

        if (substr($this->getServerResponse(),0,3) != '220') {
            throw new \Exception("Server did not respond properly: ".$this->currentResponse);
        }
    }

    private function auth()
    {
        $this->putRow('HELO ' . $this->localhost);

        if ($this->secure === 'tls') {
            $this->authTls();
        }

        if ($this->server === 'localhost') {
            $this->isLogin = true;
            return;
        }

        if ($this->putRow('AUTH LOGIN') != '334') {
            throw new \Exception($this->currentResponse);
        }
        if ($this->putRow(base64_encode($this->username)) != '334') {
            throw new \Exception($this->currentResponse);
        }
        if ($this->putRow(base64_encode($this->password)) != '235') {
            throw new \Exception($this->currentResponse);
        }

        $this->isLogin = true;
    }

    private function authTls()
    {
        if ($this->putRow('STARTTLS') != '220') {
            throw new \Exception($this->currentResponse);
        }
        stream_socket_enable_crypto($this->conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        if ($this->putRow('HELO ' . $this->localhost) != '250') {
            throw new \Exception($this->currentResponse);
        }
    }

    private function sendRecipients(array $recipients)
    {
        foreach($recipients as $recipient) {
            $this->putRow('RCPT TO: <'. $this->getMailAddr($recipient) .'>');
        }
    }

    private function getMailAddr(string $addr): string
    {
        if (($pos = strrpos($addr,' ')) !== false) {
            $addr = str_replace(['<','>'], '', substr($addr, $pos+1));
        }
        return $addr;
    }

    private function putRow(string $command)
    {
        fputs($this->conn, $command . self::NEWLINE);
        $this->currentResponse = $this->getServerResponse();
        return substr($this->currentResponse, 0, 3);
    }

    private function getServerResponse()
    {
        $data = '';
        while($str = fgets($this->conn, 4096)) {
            $data .= $str;
            if(substr($str,3,1) == " ") break;
        }
        $this->response[] = trim($data);
        return $data;
    }

    public function close()
    {
        if ($this->conn) {
            fputs($this->conn, 'QUIT' . self::NEWLINE);
            $this->getServerResponse();
            fclose($this->conn);
        }
    }

    function __destruct()
    {
        $this->close();
    }
}
