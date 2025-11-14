<?php
/**
 * This file is part of the Osynapsy mailer package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mailer;

/**
 * Description of Mailer
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class Mailer
{
    protected $client;
    protected $errors = [];
    private array $queue = [];

    public function __construct(Client\ClientInterface $client)
    {
        $this->client = $client;
    }

    // -------------------------------------------------
    // ✅ coda
    public function queue(Email\Message $msg)
    {
        $this->queue[] = $msg;
        return $this;
    }

    // -------------------------------------------------
    // ✅ Invio della coda temporanea
    public function sendQueue()
    {
        foreach ($this->queue as $msg) {
            try {
                $this->cn->sendMessage($msg);
            } catch (\Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        }
        $this->queue = [];
        return $this;
    }

    public function send(Email\Message $msg): self
    {
        try {
            $this->client->sendMessage($msg);
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        return $this;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
