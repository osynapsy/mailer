<?php
namespace Osynapsy\Mailer\Email;

class TransitionalMessage extends Message
{
    private array $placeholders = [];

    public function setPlaceholder(string $key, string $value): self
    {
        $this->placeholders[$key] = $value;
        return $this;
    }

    public function setPlaceholders(array $data): self
    {
        foreach ($data as $key => $value) {
            $this->setPlaceholder($key, $value);
        }
        return $this;
    }

    public function getProcessedBody($rawbody): string
    {

        // Trova tutti i placeholder del tipo {key|default} o {key}
        $body = preg_replace_callback('/\{(\w+)(?:\|([^}]+))?\}/', function($matches) {
            $key = $matches[1];
            $default = $matches[2] ?? '';
            return $this->placeholders[$key] ?? $default;
        }, $rawbody);

        return $body;
    }

    public function __toString(): string
    {
        if (!empty($this->getPlainBody())) {
            $this->setPlainBody(
                $this->getProcessedBody(
                    $this->getPlainBody()
                )
            );
        }
        if (!empty($this->getHtmlBody())) {
            $this->setHtmlBody(
                $this->getProcessedBody(
                    $this->getHtmlBody()
                )
            );
        }
        return parent::__toString();
    }
}

