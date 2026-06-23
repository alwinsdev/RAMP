<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Imagick;
use ImagickPixel;

/**
 * Generates the favicon set from the brand logo at public/images/ramp-logo.png.
 *
 * The logo is a landscape lockup, so each icon is produced by trimming the white
 * border, scaling to fit, and padding to a transparent square — keeping the whole
 * mark visible (never cropped/squished). Run after saving/updating the logo:
 *
 *     php artisan ramp:favicons
 */
final class MakeFavicons extends Command
{
    protected $signature = 'ramp:favicons';

    protected $description = 'Generate favicons (16/32/180 + .ico) from public/images/ramp-logo.png';

    /** size => output filename */
    private const TARGETS = [
        32 => 'favicon-32.png',
        16 => 'favicon-16.png',
        180 => 'apple-touch-icon.png',
    ];

    public function handle(): int
    {
        $source = public_path('images/ramp-logo.png');

        if (! is_file($source)) {
            $this->error("Logo not found at: {$source}");
            $this->line('Save your logo image there first, then re-run this command.');

            return self::FAILURE;
        }

        if (! extension_loaded('imagick')) {
            $this->error('The imagick PHP extension is required to generate favicons.');

            return self::FAILURE;
        }

        $logo = new Imagick($source);
        $logo->setImageBackgroundColor(new ImagickPixel('white'));

        // Trim the surrounding white so the icon is tightly framed.
        try {
            $logo->trimImage(0.12 * (float) $logo->getQuantumRange()['quantumRangeLong']);
            $logo->setImagePage(0, 0, 0, 0);
        } catch (\Throwable) {
            // If trimming fails, fall back to the untrimmed image.
        }

        foreach (self::TARGETS as $size => $filename) {
            $icon = clone $logo;
            $icon->setImageBackgroundColor(new ImagickPixel('transparent'));
            $icon->resizeImage($size, $size, Imagick::FILTER_LANCZOS, 1, true); // bestfit

            $canvas = new Imagick();
            $canvas->newImage($size, $size, new ImagickPixel('transparent'), 'png');
            $x = (int) (($size - $icon->getImageWidth()) / 2);
            $y = (int) (($size - $icon->getImageHeight()) / 2);
            $canvas->compositeImage($icon, Imagick::COMPOSITE_OVER, $x, $y);
            $canvas->setImageFormat('png32');
            $canvas->writeImage(public_path("images/{$filename}"));

            $this->line("  wrote images/{$filename} ({$size}x{$size})");
        }

        // Multi-resolution .ico at the document root (browsers request /favicon.ico).
        $ico = new Imagick();
        foreach ([16, 32, 48] as $size) {
            $frame = clone $logo;
            $frame->setImageBackgroundColor(new ImagickPixel('transparent'));
            $frame->resizeImage($size, $size, Imagick::FILTER_LANCZOS, 1, true);
            $square = new Imagick();
            $square->newImage($size, $size, new ImagickPixel('transparent'), 'png');
            $square->compositeImage(
                $frame,
                Imagick::COMPOSITE_OVER,
                (int) (($size - $frame->getImageWidth()) / 2),
                (int) (($size - $frame->getImageHeight()) / 2),
            );
            $square->setImageFormat('ico');
            $ico->addImage($square);
        }
        $ico->setImageFormat('ico');
        $ico->writeImages(public_path('favicon.ico'), true);
        $this->line('  wrote favicon.ico (16/32/48)');

        $this->info('Favicons generated from images/ramp-logo.png.');

        return self::SUCCESS;
    }
}
