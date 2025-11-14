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

/**
 * Description of Message
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class Message
{
    protected string $from;
    protected array $to = [];
    protected array $cc = [];
    protected array $bcc = [];
    protected string $subject;
    protected string $plainBody = '';
    protected string $htmlBody = '';
    protected string $altBody = '';
    protected bool $isHtml = true;
    protected array $attachments = [];

    public function __construct(string $from, string $to, string $subject, string $body = '')
    {
        $this->from = $from;
        $this->addTo($to);
        $this->subject = $subject;
        $this->setPlainBody($body);
    }

    public function addCC(string $email): self
    {
        $this->cc[] = $email;
        return $this;
    }

    public function getCc()
    {
        return $this->cc;
    }

    public function addBCC(string $email): self
    {
        $this->bcc[] = $email;
        return $this;
    }

    public function getBcc()
    {
        return $this->bcc;
    }

    public function addTo($to)
    {
        $this->to[] = $to;
        return $this;
    }

    public function getAttachments()
    {
        return $this->attachments;
    }

    public function addAttachment(Attachment $attachment): self
    {
        $this->attachments[] = $attachment;
        return $this;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function getPlainBody()
    {
        return $this->plainBody;
    }

    public function setPlainBody($plainBody)
    {
        $this->plainBody = $plainBody;
        return $this;
    }

    public function getHtmlBody()
    {
        return $this->htmlBody;
    }

    public function setHtmlBody($htmlBody)
    {
        $this->isHtml = true;
        $this->htmlBody = $htmlBody;
        if (!$this->plainBody) {
            $this->setPlainBody($this->generatePlainFromHtml($htmlBody));
        }
        return $this;
    }

    protected function generatePlainFromHtml(string $html): string
    {
        // Rimuovi tag, decodifica entità, normalizza spazi
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    // -------------------------------------------------
    // Restituisce la mail in formato raw MIME (per debug o log)
    public function __toString(): string
    {
        return strval(new MessageBuilder($this));
    }
}
