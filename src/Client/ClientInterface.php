<?php
/**
 * This file is part of the Osynapsy mailer package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
