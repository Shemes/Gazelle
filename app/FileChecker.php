<?php

namespace Gazelle;

class FileChecker
{
    private $comicExtensions;
    private $musicExtensions;
    private $badExtensions;
    private $keywords;

    public function __construct()
    {
        $this->comicExtensions = array_fill_keys(['cbr', 'cbz', 'gif', 'jpeg', 'jpg', 'pdf', 'png'], true);
        $this->musicExtensions = array_fill_keys(
            [
                'ac3', 'accurip', 'azw3', 'chm', 'cue', 'djv', 'djvu', 'doc', 'docx', 'dts', 'epub', 'ffp',
                'flac', 'gif', 'htm', 'html', 'jpeg', 'jpg', 'lit', 'log', 'm3u', 'm3u8', 'm4a', 'm4b',
                'md5', 'mobi', 'mp3', 'mp4', 'nfo', 'pdf', 'pls', 'png', 'rtf', 'sfv', 'txt',
            ],
            true
        );
        $this->badExtensions = array_fill_keys([
            'torrent',
        ], true);

        $this->keywords = [
            'ahashare.com', 'demonoid.com', 'demonoid.me', 'djtunes.com', 'h33t', 'housexclusive.net',
            'limetorrents.com', 'mixesdb.com', 'mixfiend.blogstop', 'mixtapetorrent.blogspot',
            'plixid.com', 'reggaeme.com', 'scc.nfo', 'thepiratebay.org', 'torrentday',
        ];
    }

    // check_file
    public function checkFile($type, $name)
    {
        $this->checkName($name);
        $this->checkExtensions($type, $name);
    }

    // check_name
    public function checkName($name)
    {
        foreach ($this->keywords as &$Value) {
            if (strpos(strtolower($name), $Value) !== false) {
                $this->forbiddenError($name);
            }
        }

        /*
        * These characters are invalid in NTFS on Windows systems:
        *		: ? / < > \ * | "
        * TODO: Add "/" to the blacklist. Adding "/" to the blacklist causes problems with nested dirs, apparently.
        * Only the following characters need to be escaped (see the link below):
        *		\ - ^ ]
        * http://www.php.net/manual/en/regexp.reference.character-classes.php
        */
        $bloquedChars = ' : ? < > \ * | " ';
        if (preg_match('/[\\:?<>*|"]/', $name, $matches)) {
            $this->characterError($matches[0], $bloquedChars);
        }
    }

    // check_extensions
    private function checkExtensions($type, $name)
    {
        $extension = $this->getFileExtension($name);
        if ($type == 'Music' || $type == 'Audiobooks' || $type == 'Comedy' || $type == 'E-Books') {
            if (!isset($this->musicExtensions[$extension])) {
                $this->invalidError($name);
            }
        } elseif ($type == 'Comics') {
            if (!isset($this->comicExtensions[$extension])) {
                $this->invalidError($name);
            }
        } else {
            if (isset($this->badExtensions[$extension])) {
                $this->forbiddenError($name);
            }
        }
    }

    // get_file_extension
    private function getFileExtension($fileName)
    {
        return strtolower(substr(strrchr($fileName, '.'), 1));
    }

    // invalid_error
    private function invalidError($name)
    {
        global $Err;
        $Err = 'The torrent contained one or more invalid files (' . display_str($name) . ')';
    }

    //forbidden_error
    private function forbiddenError($name)
    {
        global $Err;
        $Err = 'The torrent contained one or more forbidden files (' . display_str($name) . ')';
    }

    // character_error
    private function characterError($Character, $blockedChars)
    {
        global $Err;
        $Err = "One or more of the files or folders in the torrent has a name that contains the forbidden character '$Character'. Please rename the files as necessary and recreate the torrent.<br /><br />\nNote: The complete list of characters that are disallowed are shown below:<br />\n\t\t$blockedChars";
    }
}
