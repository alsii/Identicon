<?php

namespace Identicon\Generator;

use Identicon\Generator\GeneratorInterface;

/**
 * @author Francis Chuang <francis.chuang@gmail.com>
 */
class ImageMagickGenerator extends BaseGenerator implements GeneratorInterface
{
    public function __construct()
    {
        if (!extension_loaded('imagick')) {
            throw new \Exception('ImageMagick does not appear to be avaliable in your PHP installation. Please try another generator');
        }
    }

    private function generateImage()
    {
        $this->generatedImage = new \Imagick();
        $rgbBackgroundColor = $this->getBackgroundColor();

        if (null === $rgbBackgroundColor) {
            $background = 'none';
        } else {
            $background = new \ImagickPixel("rgb($rgbBackgroundColor[0],$rgbBackgroundColor[1],$rgbBackgroundColor[2])");
        }

        $this->generatedImage->newImage($this->pixelRatio * 5 + $this->getMarginSize() * 2, $this->pixelRatio * 5  + $this->getMarginSize() * 2, $background, 'png');

        // prepare color
        $rgbColor = $this->getColor();
        $color = new \ImagickPixel("rgb($rgbColor[0],$rgbColor[1],$rgbColor[2])");

        $draw = new \ImagickDraw();
        $draw->setFillColor($color);

        // draw the content
        foreach ($this->getArrayOfSquare() as $lineKey => $lineValue) {
            foreach ($lineValue as $colKey => $colValue) {
                if (true === $colValue) {
                    $draw->rectangle( $colKey * $this->pixelRatio + $this->getMarginSize(), 
                    		$lineKey * $this->pixelRatio + $this->getMarginSize(), 
                    		($colKey + 1) * $this->pixelRatio + $this->getMarginSize(), 
                    		($lineKey + 1) * $this->pixelRatio + $this->getMarginSize());
                }
            }
        }

        $this->generatedImage->drawImage($draw);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getImageBinaryData($string, $size = null, $color = null, $backgroundColor = null, $marginSize = null)
    {
        ob_start();
        echo $this->getImageResource($string, $size, $color, $backgroundColor, $marginSize);
        $imageData = ob_get_contents();
        ob_end_clean();

        return $imageData;
    }

    /**
     * {@inheritDoc}
     */
    public function getImageResource($string, $size = null, $color = null, $backgroundColor = null, $marginSize = null)
    {
        $this
            ->setString($string)
            ->setSize($size)
            ->setColor($color)
            ->setBackgroundColor($backgroundColor)
            ->setMarginSize($marginSize)
            ->generateImage();

        return $this->generatedImage;
    }
}
