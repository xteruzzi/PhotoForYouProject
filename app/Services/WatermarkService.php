<?php

namespace App\Services;

class WatermarkService
{
    /**
     * Applique un filigrane sur l'image publique.
     * Retourne le nom du fichier filigrané (stocké dans public/images/).
     */
    public function apply(string $filename): string
    {
        $sourcePath = public_path('images/' . $filename);
        $ext        = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!function_exists('imagecreatefromjpeg') || !file_exists($sourcePath)) {
            return $filename; // GD absent ou fichier introuvable
        }

        if ($ext === 'png') {
            $image = imagecreatefrompng($sourcePath);
            imagesavealpha($image, true);
        } else {
            $image = imagecreatefromjpeg($sourcePath);
        }

        if (!$image) {
            return $filename;
        }

        $width  = imagesx($image);
        $height = imagesy($image);

        // Couleurs semi-transparentes
        $blanc = imagecolorallocatealpha($image, 255, 255, 255, 25);
        $noir  = imagecolorallocatealpha($image, 0,   0,   0,   40);

        $text  = '© PhotoForYou - AtelierLumière - Aperçu seul';
        $font  = 5; // police GD intégrée (max = 5)

        $textW = imagefontwidth($font) * strlen($text);
        $textH = imagefontheight($font);

        // Texte centré principal
        $x = max(0, (int)(($width  - $textW) / 2));
        $y = max(0, (int)(($height - $textH) / 2));
        imagestring($image, $font, $x + 2, $y + 2, $text, $noir);
        imagestring($image, $font, $x,     $y,     $text, $blanc);

        // Répétition en diagonale toutes les 200px
        $shortText = 'PhotoForYou';
        $step = 200;
        for ($py = 0; $py < $height; $py += $step) {
            for ($px = -100; $px < $width; $px += $step) {
                imagestring($image, 2, $px, $py, $shortText, $blanc);
            }
        }

        $wmName = 'wm_' . $filename;
        $dest   = public_path('images/' . $wmName);

        if ($ext === 'png') {
            imagepng($image, $dest);
        } else {
            imagejpeg($image, $dest, 82);
        }

        imagedestroy($image);
        return $wmName;
    }
}
