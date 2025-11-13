<?php
namespace Osynapsy\Mailer\Client;

use Osynapsy\Mailer\Email\Message;

/**
 * Description of ClientInterface
 *
 * @author Pietro Celeste <p.celeste@qanda.cc>
 */
interface ClientInterface
{
    public function sendMessage(Message $msg);
}
