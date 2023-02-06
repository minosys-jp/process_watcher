<?php

namespace App\Libs;

class Common {
    static public function expandBreads($breads) {
        $s = '';
        foreach ($breads as $key => $url) {
	    if (!empty($s)) {
                $s .= " &gt; ";
            }
            $s .= "<a href='$url'>$key</a>";
        }
        return $s;
    }
}
