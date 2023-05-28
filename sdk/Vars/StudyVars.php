<?php

namespace Devine\NihongoStudySdk\Vars;

class StudyVars
{
    const IGNORE = 0;
    const FORGET = 1;
    const BLURRY = 2;
    const REMEBER = 3;

    const FORGET_TIME = 60 * 30;
    const BLURRY_TIME = 60 * 60 * 4;
    const REMEBER_TIME = 60 * 60 * 24 * 4;
}
