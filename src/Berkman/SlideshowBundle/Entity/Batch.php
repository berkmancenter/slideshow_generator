<?php

namespace Berkman\SlideshowBundle\Entity;

class Batch
{
    private $progress;
    private $file;
    private $session;

    public function __construct(\SPLFileObject $file, $session)
    {
        $this->file = $file;
        $this->session = $session;
    }

    public function getProgress()
    {
        return $this->progress;
    } 

    public function setProgress($progress)
    {
        $this->progress = $progress;
        $this->session->set('progress', $progress);
    }

    public function getFile()
    {
        return $this->file;
    }
}
