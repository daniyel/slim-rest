<?php

namespace Src\Helper;

class Money {

    /**
     * @param integer $value
     *
     * @return integer
     */
    public static function prepareForDb(int $value): integer {
        if (empty($value)) {
            return $value;
        }
        return $value * 100;
    }

    /**
     * @param integer $value
     *
     * @return string
     */
    public static function prepareForPresentation(int $value): string {
        if (empty($value)) {
            return $value;
        }
        return number_format($value / 100, 2);
    }
}
