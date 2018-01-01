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
    public function calculate($p1, $p2)
    {
        $w = abs($p1[0]-$p2[0]);
        $h = abs($p1[1]-$p2[1]);

        return round(sqrt(($w*$w)+($h*$h))*1.76/1000, 5);
    }
}
