<?php
namespace Osynapsy\Mailer\Email;

/**
 * Description of Attachment
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class Attachment 
{
    protected $filepath;
    protected $filename;
    protected $type;
    protected $content;
    
    public function __construct($path, $content = null)
    {
        $this->filepath = $path;
        $this->content = $content;
    }
    
    public function getContent()
    {
        return $this->content ?? file_get_contents($this->filepath);
    }
    
    public function getFilename()
    {
        return basename($this->filepath);
    }
    
    public function getType()
    {    
        $mine = new \finfo(\FILEINFO_MIME_TYPE);
        return $mine->file($this->filepath);    
    }
    
    public function __toString()
    {
        return strval(new AttachmentBuilder($this));
    }
}
