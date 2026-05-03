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
        // Définition du chemin complet de l'image source sur le serveur
        $sourcePath = public_path('images/' . $filename);
        // Récupération de l'extension pour adapter le traitement (jpg, png...)
        $ext         = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Vérification de sécurité : la librairie GD est-elle installée ? Le fichier existe-t-il ?
        if (!function_exists('imagecreatefromjpeg') || !file_exists($sourcePath)) {
            return $filename; // On retourne l'original si on ne peut pas traiter
        }

        // Création d'une ressource image en mémoire selon le format
        if ($ext === 'png') {
            $image = imagecreatefrompng($sourcePath);
            // Conserve la transparence propre au format PNG
            imagesavealpha($image, true);
        } else {
            $image = imagecreatefromjpeg($sourcePath);
        }

        // Si la création de l'image a échoué (fichier corrompu par exemple)
        if (!$image) {
            return $filename;
        }

        // Récupération des dimensions (largeur et hauteur) de l'image source
        $width  = imagesx($image);
        $height = imagesy($image);

        // Allocation des couleurs avec gestion de l'alpha (transparence)
        // Les paramètres sont : (image, rouge, vert, bleu, transparence de 0 à 127)
        $blanc = imagecolorallocatealpha($image, 255, 255, 255, 25);
        $noir  = imagecolorallocatealpha($image, 0,   0,   0,   40);

        // Configuration du texte principal du filigrane
        $text  = '© PhotoForYou - AtelierLumière - Aperçu seul';
        $font  = 5; // Utilisation de la police système intégrée à PHP (la plus grande disponible)

        // Calcul de la taille du texte pour pouvoir le centrer parfaitement
        $textW = imagefontwidth($font) * strlen($text);
        $textH = imagefontheight($font);

        // Calcul des coordonnées X et Y pour le centrage
        $x = max(0, (int)(($width  - $textW) / 2));
        $y = max(0, (int)(($height - $textH) / 2));
        
        // Dessin du texte : on dessine d'abord l'ombre (noir) puis le texte (blanc) décalé
        // Cela crée un effet de contour qui rend le texte lisible sur n'importe quel fond
        imagestring($image, $font, $x + 2, $y + 2, $text, $noir);
        imagestring($image, $font, $x,     $y,     $text, $blanc);

        // Répétition d'un petit texte en motif de fond (diagonale) pour protéger toute l'image
        $shortText = 'PhotoForYou';
        $step = 200; // Espace entre chaque répétition (200 pixels)
        for ($py = 0; $py < $height; $py += $step) {
            for ($px = -100; $px < $width; $px += $step) {
                // Utilisation d'une police plus petite (taille 2) pour le motif répété
                imagestring($image, 2, $px, $py, $shortText, $blanc);
            }
        }

        // Préparation du nouveau nom de fichier (préfixé par wm_)
        $wmName = 'wm_' . $filename;
        $dest   = public_path('images/' . $wmName);

        // Sauvegarde physique de l'image modifiée sur le disque
        if ($ext === 'png') {
            imagepng($image, $dest);
        } else {
            // Pour le JPEG, on définit une qualité de 82% pour optimiser le poids
            imagejpeg($image, $dest, 82);
        }

        // Libération de la mémoire vive utilisée par la ressource image
        imagedestroy($image);
        
        // On retourne le nom du nouveau fichier créé
        return $wmName;
    }
}