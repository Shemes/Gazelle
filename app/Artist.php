<?php

namespace Gazelle;

class Artist
{
    public $ID = 0;
    public $Name = 0;
    public $NameLength = 0;
    public $SimilarID = 0;
    public $Displayed = false;
    public $x = 0;
    public $y = 0;
    public $Similar = [];

    public function __construct($ID = '', $Name = '')
    {
        $this->ID = $ID;
        $this->NameLength = mb_strlen($Name, 'utf8');
        $this->Name = display_str($Name);
    }
}
