<?php

namespace Devine\NihongoStudySdk\Helps;

class DictHelp
{
    const AUDIO_URL = "http://voice.file.ecomaping.com/book01-lesson%s-%s.mp3";

    public static function getAudioUrl($lessonNum, $idx)
    {
        $idx--;
        $lessonNum = str_pad($lessonNum, 2, '0', STR_PAD_LEFT);
        $idxNum = str_pad($idx, 2, '0', STR_PAD_LEFT);
        return sprintf(self::AUDIO_URL, $lessonNum, $idxNum);
    }

}