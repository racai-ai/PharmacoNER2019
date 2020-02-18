<?php

function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

function startsWithIgnoreCase($haystack, $needle)
{
     $length = strlen($needle);
     return (strcasecmp(substr($haystack, 0, $length) , $needle)==0);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

