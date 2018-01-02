<?php
/**
 * Calculate Trait.
 * 
 * @author herman <i@imzsy.com>
 */

namespace WechatJumper;

trait CalculateTrait
{
    /**
     * Calculate time.
     *
     * @param array $p1 point
     * @param array $p2 point
     *
     * @return float
     */
    public function calculate($p1, $p2, $co = 2.0)
    {
        $w = abs($p1[0]-$p2[0]);
        $h = abs($p1[1]-$p2[1]);
        $length = sqrt(($w*$w)+($h*$h));
        // $co = $length > 350 ? $co + 0.05 : $co;
        // $co = $length < 200 ? $co - 0.05 : $co;

        return $length*$co/1000;
    }
}
