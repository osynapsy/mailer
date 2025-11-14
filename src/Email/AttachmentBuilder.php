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
 * Description of AttachmentBuilder
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class AttachmentBuilder extends AbstractBuilder
{
    protected $attachment;

    public function __construct(Attachment $attachment)
    {
        $this->attachment = $attachment;
    }

    public function build() : string
    {
        $attachment = $this->attachment;
        $result = $this->row('Content-Type', $attachment->getType(), sprintf('name="%s"', $attachment->getFilename()));
        $result .= $this->row('Content-Transfer-Encoding', 'base64');
        $result .= $this->row('Content-Disposition', 'attachment', sprintf('filename="%s"', $attachment->getFilename()));
        $result .= self::NEWLINE;
        $result .= chunk_split(base64_encode($attachment->getContent()));
        return $result;
    }
}
