<?php
namespace Osynapsy\Mailer\Email\Builder;

/**
 * Description of AbstractBuilder
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
abstract class AbstractBuilder
{
    const NEWLINE = "\r\n";

    public function row($label, $value, $postfix = '')
    {
        $row = [sprintf('%s: %s', $label, $value)];
        if (!empty($postfix)) {
            $row[] = $postfix;
        }
        return trim(implode('; ', $row)) . self::NEWLINE;
    }

    public function rawrow($value)
    {
        return $value . self::NEWLINE;
    }

    public function __toString(): string
    {
        try {
           return $this->build();
        } catch (\Throwable $e) {
            return 'Error building message: ' . $e->getMessage();
        }
    }

    abstract public function build() : string;
}
