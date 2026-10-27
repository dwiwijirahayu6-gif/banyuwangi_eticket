<?php

namespace App\Modules\Admin\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

final class QrCodeSvg
{
    public function render(string $url, int $size = 480): string
    {
        $renderer = new ImageRenderer(new RendererStyle($size, 2), new SvgImageBackEnd);
        return (new Writer($renderer))->writeString($url);
    }
}