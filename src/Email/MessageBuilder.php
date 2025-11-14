<?php
/**
 * This file is part of the Osynapsy mailer package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mailer\Email;

use Osynapsy\Mailer\Email\Builder\AbstractBuilder;

/**
 * Description of MessageBuilder
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class MessageBuilder extends AbstractBuilder
{

    private string $boundary;
    private string $altBoundary;
    private $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->boundary = 'bnd_' . md5(uniqid('', true));
        $this->altBoundary = 'alt_' . $this->boundary;
    }

    public function build(): string
    {
        $cnt = $this->buildHeader($this->message);
        if ($this->message->getHtmlBody()) {
            $cnt .= $this->buildBoundary($this->boundary, $this->altBoundary);
            $cnt .= $this->rawrow('--' . $this->altBoundary);
            $cnt .= $this->buildBody('text/plain', $this->message->getPlainBody());
            $cnt .= $this->rawrow('');
            $cnt .= $this->rawrow('--' . $this->altBoundary);
            $cnt .= $this->buildBody('text/html', $this->message->getHtmlBody());
            $cnt .= $this->rawrow('--' . $this->altBoundary . '--');
        } else {
            $cnt .= $this->rawrow('--' . $this->boundary);
            $cnt .= $this->buildBody('text/plain', $this->message->getPlainBody());
        }
        $cnt .= $this->rawrow('');
        // ---- Allegati (opzionali) ----
        $cnt .= $this->buildAttachments($this->message->getAttachments());
        // ---- Fine ----
        $cnt .= $this->rawrow('--' . $this->boundary . '--');
        return $cnt;
    }

    protected function buildBoundary($boundary, $altBoundary)
    {
        $strBoundary = $this->rawrow('--' . $boundary);
        $strBoundary .= $this->row('Content-Type', 'multipart/alternative', 'boundary="' . $altBoundary . '"');
        $strBoundary .= $this->rawrow('');
        return $strBoundary;
    }

    protected function buildHeader($msg)
    {
        $headers = $this->row('Date', date('r'));
        $headers .= $this->row('From', $msg->getFrom());
        $headers .= $this->row('To', implode(', ', $msg->getTo()));
        if ($msg->getCc()) {
            $headers .= $this->row('Cc', implode(', ', $msg->getCc()));
        }
        if ($msg->getBcc()) {
            $headers .= $this->row('Bcc', implode(', ', $msg->getBcc()));
        }
        $headers .= $this->row('Subject', $msg->getSubject());
        $headers .= $this->row('MIME-Version', '1.0');
        $headers .= $this->row('Content-Type', 'multipart/mixed', 'boundary="' . $this->boundary . '"');
        $headers .= self::NEWLINE;
        return $headers;
    }

    protected function buildAttachments($attachments)
    {
        $strAttachments = '';
        foreach ($attachments as $attachment) {
            $strAttachments .= $this->rawrow('--' . $this->boundary);
            $strAttachments .= strval($attachment);
            $strAttachments .= $this->rawrow('');
        }
        return $strAttachments;
    }

    protected function buildBody($contentType, $body)
    {
        $strBody = $this->row('Content-Type', $contentType, 'charset="utf-8"');
        $strBody .= $this->row('Content-Transfer-Encoding', '8bit');
        $strBody .= $this->rawrow('');
        $strBody .= $body;
        $strBody .= $this->rawrow('');
        return $strBody;
    }
}
