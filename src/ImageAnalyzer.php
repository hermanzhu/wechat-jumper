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
    protected $currentColorSample = [148, 137, 177];
    protected $currentColorSampleBottom = [56, 52, 95];
    // protected $currentColorSample = [56, 52, 95];

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
        for ($height = 860; $height >= 540; $height --) {
            for ($width = 20; $width <= 700; $width ++) {
                $rgb = $this->colorAt($width, $height);
                $rgbBottom = $this->colorAt($width-10, $height+66);
                if ($this->colorSimilar($rgb, $this->currentColorSample, 8)
                    && $this->colorSimilar($rgbBottom, $this->currentColorSampleBottom, 15)
                ) {
                    return [$width-10, $height+66];
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
                for ($width = 700; $width >= 370; $width--) {
                    //在范围内逐行扫描，找到最高点
                    $rgb = $this->colorAt($width, $height);
                    //和底色以及人物颜色误差超过阈值则认为是目标点最高点
                    if ($this->colorHugeSimilar($rgb, $this->colorAt(700, $height), 25)
                        && !$this->colorSimilar($rgb, $this->currentColorSampleBottom, 10)
                    ) {
                        $need = $this->colorAt($width, $height+10);
                        // dump($width, $height, $need, $rgb, $this->colorAt(700, $height));
                        //根据需要的颜色从又往左寻找最右侧的相似点
                        for ($x = 700; $x >= 360; $x --) {
                            for ($y = 470; $y <= 615; $y ++) {
                                if ($this->colorSimilar($need, $this->colorAt($x, $y), 7)) {
                                    return [$width, $y];
                                }
                            }
                        }
                    }
                }
            }
        } else {
            // 人物在右侧
            for ($height = 470; $height <= 615; $height++) {
                for ($width = 20; $width <= 350; $width++) {
                    //在范围内逐行扫描，找到最高点
                    $rgb = $this->colorAt($width, $height);
                    //和底色以及人物颜色误差超过阈值则认为是目标点最高点
                    if ($this->colorHugeSimilar($rgb, $this->colorAt(700, $height), 25)
                        && !$this->colorSimilar($rgb, $this->currentColorSampleBottom, 10)
                    ) {
                        $need = $this->colorAt($width, $height+10);
                        // dump($width, $height, $need, $rgb, $this->colorAt(700, $height));
                        //根据需要的颜色从左往右寻找最右侧的相似点
                        for ($x = 20; $x <= 360; $x ++) {
                            for ($y = 470; $y <= 615; $y ++) {
                                if ($this->colorSimilar($need, $this->colorAt($x, $y), 7)) {
                                    return [$width, $y];
                                }
                            }
                        }
                    }
                }
            }
        }

        throw new \Exception('未找到目标位的坐标，可能需要调整参数，可能需要收集场景图片……');
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
     * Caculate huge similar.
     *
     * @param array $rgb1 color1
     * @param array $rgb2 color2
     * @param int   $gap  gap
     * 
     * @return boolean
     */
    protected function colorHugeSimilar($rgb1, $rgb2, $gap)
    {
        return (abs($rgb1[0]-$rgb2[0]) > $gap)
            || (abs($rgb1[1]-$rgb2[1]) > $gap)
            || (abs($rgb1[2]-$rgb2[2]) > $gap);
    }

    /**
     * Get bg color
     *
     * @param int $width  width
     * @param int $height height
     *
     * @return array
     */
    protected function colorAt($width, $height)
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
        list($width, $height) = getimagesize($file);
        $newHeight = 720/($width/$height);
        $src = imagecreatefrompng($file);
        $to = imagecreatetruecolor(720, $newHeight);
        $new = imagecopyresampled($to, $src, 0, 0, 0, 0, 720, $newHeight, $width, $height);
        imagepng($to, $file);

        return imagecreatefrompng($file);
    }
}
