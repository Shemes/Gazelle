<?php

namespace Gazelle;

/**
 * The encode class is simple and straightforward. The only thing to
 * note is that empty dictionaries are represented by boolean trues
 */
class Bencode
{
    private $DefaultKeys = [ // Get rid of everything except these keys to save some space
        'created by',
        'creation date',
        'encoding',
        'info',
    ];
    private $Data;
    public $Enc;

    /**
     * Encode an arbitrary array (usually one that's just been decoded)
     *
     * @param array $Arg the thing to encode
     * @param mixed $Keys string or array with keys in the input array to encode or true to encode everything
     * @return bencoded string representing the content of the input array
     */
    public function encode($Arg = false, $Keys = false)
    {
        if ($Arg === false) {
            $Data = &$this->Dec;
        } else {
            $Data = &$Arg;
        }
        if ($Keys === true) {
            $this->Data = $Data;
        } elseif ($Keys === false) {
            $this->Data = array_intersect_key($Data, array_flip($this->DefaultKeys));
        } elseif (is_array($Keys)) {
            $this->Data = array_intersect_key($Data, array_flip($Keys));
        } else {
            $this->Data = isset($Data[$Keys]) ? $Data[$Keys] : false;
        }
        if (!$this->Data) {
            return false;
        }
        $this->Enc = $this->_benc();
        return $this->Enc;
    }

    /**
     * Internal encoding function that does the actual job
     *
     * @return bencoded string
     */
    private function _benc()
    {
        if (!is_array($this->Data)) {
            if (\Gazelle\Int64::is_int($this->Data)) { // Integer
                return 'i' . \Gazelle\Int64::get($this->Data) . 'e';
            }
            if ($this->Data === true) { // Empty dictionary
                return 'de';
            }
            return strlen($this->Data) . ':' . $this->Data; // String
        }
        if (empty($this->Data) || \Gazelle\Int64::is_int(key($this->Data))) {
            $IsDict = false;
        } else {
            $IsDict = true;
            ksort($this->Data); // Dictionaries must be sorted
        }
        $Ret = $IsDict ? 'd' : 'l';
        foreach ($this->Data as $Key => $Value) {
            if ($IsDict) {
                $Ret .= strlen($Key) . ':' . $Key;
            }
            $this->Data = $Value;
            $Ret .= $this->_benc();
        }
        return $Ret . 'e';
    }
}