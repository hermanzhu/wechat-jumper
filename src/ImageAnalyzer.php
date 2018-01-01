<?php

/**
 * Image Analyzer.
 * 
 * @author herman <i@imzsy.com>
 */

namespace WechatJumper;

/**
 * ImageAnalyzer
 * 
 * Current: 56 56 98
 */
class ImageAnalyzer
{
    protected $currentColorSample = [56, 55, 97];

    /**
     * Construct
     *
     * @param string $file image file
     */
    public function __construct($file)
    {
        //resize image to 720p

        $this->source = $this->dealWithImage($file);
    }

    
    /**
     * Find current coord.
     *
     * @return array
     */
    public function findCurrent()
    {
        for ($height = 860; $height >= 630; $height --) {
            for ($width = 20; $width <=700; $width ++) {
                $rgb = $this->rgbArray(imagecolorat($this->source, $width, $height));
                if ($this->colorSimilar($rgb, $this->currentColorSample, 3)) {
                    return [$width, $height];
                }
            }
        }

        throw new \Exception('未找到当前坐标，可能需要调整参数……');
    }

    /**
     * Find target coord.
     *
     * @return array
     */
    public function findTarget()
    {
        // 人物在左侧从右向左扫描，否则从左向右
        $cp = $this->findCurrent();
        if ($cp[0] < 360) {
            // 人物在左侧
            for ($height = 470; $height <= 615; $height++) {
                for ($width = 700; $width >= 360; $width--) {
                    //在范围内逐行扫描，找到最高点
                    $rgb = $this->rgbArray(imagecolorat($this->source, $width, $height));
                    //和底色以及人物颜色误差超过阈值则认为是目标点最高点
                    if (!$this->colorSimilar($rgb, $this->bgColor($width-30, $height), 10)
                        && !$this->colorSimilar($rgb, $this->currentColorSample, 30)
                    ) {
                        //从下往上扫描接近这个颜色的点
                        $need = $rgb;
                        for ($anotherHeight = 615; $anotherHeight > $height; $anotherHeight--) {
                            $rgb = $this->rgbArray(imagecolorat($this->source, $width, $anotherHeight));
                            if ($this->colorSimilar($rgb, $need, 4)) {
                                return [$width, (int) round(($height+$anotherHeight)/2)];
                            }
                        }
                    }
                }
            }
        } else {
            // 人物在右侧
            for ($height = 470; $height <= 615; $height++) {
                for ($width = 20; $width <= 360; $width++) {
                    //在范围内逐行扫描，找到最高点
                    $rgb = $this->rgbArray(imagecolorat($this->source, $width, $height));
                    //和底色以及人物颜色误差超过阈值则认为是目标点最高点
                    if (!$this->colorSimilar($rgb, $this->bgColor($width+30, $height), 10)
                        && !$this->colorSimilar($rgb, $this->currentColorSample, 30)
                    ) {
                        //从下往上扫描接近这个颜色的点
                        $need = $rgb;
                        for ($anotherHeight = 635; $anotherHeight > $height; $anotherHeight--) {
                            $rgb = $this->rgbArray(imagecolorat($this->source, $width, $anotherHeight));
                            if ($this->colorSimilar($rgb, $need, 6)) {
                                return [$width, (int) round(($height+$anotherHeight)/2)];
                            }
                        }
                    }
                }
            }
        }


        throw new \Exception('未找到目标位的坐标，可能需要调整参数……'); 
    }

    /**
     * Convert rgb.
     *
     * @param string $rgb color index
     * 
     * @return array
     */
    protected function rgbArray($rgb)
    {
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        return [$r, $g, $b];
    }

    /**
     * Caculate simillar.
     *
     * @param array $rgb1 color1
     * @param array $rgb2 color2
     * @param int   $gap  gap
     * 
     * @return boolean
     */
    protected function colorSimilar($rgb1, $rgb2, $gap)
    {
        return (abs($rgb1[0]-$rgb2[0]) < $gap)
            && (abs($rgb1[1]-$rgb2[1]) < $gap)
            && (abs($rgb1[2]-$rgb2[2]) < $gap);
    }

    /**
     * Get bg color
     * 
     * @param int $width  width
     * @param int $height height
     *
     * @return array
     */
    protected function bgColor($width, $height)
    {
        return $this->rgbArray(imagecolorat($this->source, $width, $height));
    }

    /**
     * Resize image to 720p.
     *
     * @param string $file image file.
     * 
     * @return resource
     */
    protected function dealWithImage($file)
    {
        copy($file, $file.'.back.png');
        list($width, $height) = getimagesize($file);
        $newHeight = 720/($width/$height);
        $src = imagecreatefrompng($file);
        $to = imagecreatetruecolor(720, $newHeight);
        $new = imagecopyresampled($to, $src, 0, 0, 0, 0, 720, $newHeight, $width, $height);
        imagepng($to, $file);
        return imagecreatefrompng($file);
    }
}