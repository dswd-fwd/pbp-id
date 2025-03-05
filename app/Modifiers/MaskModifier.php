<?php

namespace App\Modifiers;

use Imagick;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Interfaces\ModifierInterface;

class MaskModifier implements ModifierInterface
{
    public function __construct(protected mixed $mask, protected $mask_with_alpha_channel = false)
    {
    }

    public function apply(ImageInterface $image): ImageInterface
    {
        // build mask image instance
        $mask = $image->driver()->handleInput($this->mask);

        // resize mask to size of image
        $mask = $mask->resize($image->width(), $image->height());

        // enable alpha channel
        $image->core()->native()->setImageMatte(true);

        if ($this->mask_with_alpha_channel) {
            // mask with alpha channel of mask
            $image->core()->native()->compositeImage(
                $mask->core()->native(),
                Imagick::COMPOSITE_DSTIN,
                0,
                0
            );
        } else {
            // get alpha channel of original as greyscale image
            $original_alpha = clone $image->core()->native();
            $original_alpha->separateImageChannel(Imagick::CHANNEL_ALPHA);

            // use red channel from mask ask alpha
            $mask_alpha = clone $mask->core()->native();
            $mask_alpha->compositeImage($mask->core()->native(), Imagick::COMPOSITE_DEFAULT, 0, 0);
            $mask_alpha->separateImageChannel(Imagick::CHANNEL_ALL);

            // combine both alphas
            $original_alpha->compositeImage($mask_alpha, Imagick::COMPOSITE_COPYOPACITY, 0, 0);

            // mask the image with the alpha combination
            $image->core()->native()->compositeImage(
                $original_alpha,
                Imagick::COMPOSITE_DSTIN,
                0,
                0
            );
        }

        return $image;
    }
}