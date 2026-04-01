<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Photo;
use App\Models\User;
use App\Models\Categorie;
use App\Services\WatermarkService;
use Illuminate\Support\Facades\Storage;

class PhotoSeeder extends Seeder
{
    // ── Catalogue de photos de démonstration ──────────────────────────────────
    // Chaque photo utilise un "seed" Picsum pour obtenir une image cohérente.
    // URL : https://picsum.photos/seed/{seed}/1920/1280
    private array $catalogue = [
        // Paysages
        [
            'description' => 'Coucher de soleil sur les Alpes suisses',
            'categorie'   => 'Paysages',
            'prix'        => 18,
            'seed'        => 'alps',
        ],
        [
            'description' => 'Reflet d\'un lac de montagne au crépuscule',
            'categorie'   => 'Paysages',
            'prix'        => 22,
            'seed'        => 'lake',
        ],
        // Portraits
        [
            'description' => 'Portrait féminin en lumière naturelle',
            'categorie'   => 'Portraits',
            'prix'        => 30,
            'seed'        => 'portrait1',
        ],
        [
            'description' => 'Portrait masculin en studio noir et blanc',
            'categorie'   => 'Portraits',
            'prix'        => 25,
            'seed'        => 'portrait2',
        ],
        // Architecture
        [
            'description' => 'Façade d\'immeuble Haussmannien, Paris',
            'categorie'   => 'Architecture',
            'prix'        => 20,
            'seed'        => 'building1',
        ],
        [
            'description' => 'Couloir de marbre d\'un palais baroque',
            'categorie'   => 'Architecture',
            'prix'        => 35,
            'seed'        => 'building2',
        ],
        // Nature
        [
            'description' => 'Macro-photographie d\'une libellule en vol',
            'categorie'   => 'Nature',
            'prix'        => 15,
            'seed'        => 'nature1',
        ],
        [
            'description' => 'Forêt brumeuse au lever du soleil',
            'categorie'   => 'Nature',
            'prix'        => 28,
            'seed'        => 'forest',
        ],
        // Sport
        [
            'description' => 'Sprinter au départ des blocs, stade olympique',
            'categorie'   => 'Sport',
            'prix'        => 40,
            'seed'        => 'sport1',
        ],
        [
            'description' => 'Cycliste en plein effort sur col de montagne',
            'categorie'   => 'Sport',
            'prix'        => 35,
            'seed'        => 'sport2',
        ],
        // Mode
        [
            'description' => 'Éditorial mode — collection printemps-été',
            'categorie'   => 'Mode',
            'prix'        => 50,
            'seed'        => 'fashion1',
        ],
        [
            'description' => 'Shooting studio — robe de haute couture',
            'categorie'   => 'Mode',
            'prix'        => 60,
            'seed'        => 'fashion2',
        ],
        // Reportage
        [
            'description' => 'Marché de nuit à Barcelone — vie quotidienne',
            'categorie'   => 'Reportage',
            'prix'        => 20,
            'seed'        => 'street1',
        ],
        [
            'description' => 'Musicien de rue jouant du saxo sous la pluie',
            'categorie'   => 'Reportage',
            'prix'        => 25,
            'seed'        => 'street2',
        ],
        // Abstrait
        [
            'description' => 'Longue exposition — lumières d\'une autoroute',
            'categorie'   => 'Abstrait',
            'prix'        => 45,
            'seed'        => 'abstract1',
        ],
        [
            'description' => 'Jeu de miroirs et de reflets colorés',
            'categorie'   => 'Abstrait',
            'prix'        => 55,
            'seed'        => 'abstract2',
        ],
    ];

    public function run(): void
    {
        $watermark    = new WatermarkService();
        $photographe  = User::where('pseudo', 'flippo_photo')->firstOrFail();
        $categories   = Categorie::pluck('id_categorie', 'libelle');

        $imagesDir = public_path('images');
        $originalsDir = storage_path('app/originals');

        if (!is_dir($imagesDir))   mkdir($imagesDir, 0755, true);
        if (!is_dir($originalsDir)) mkdir($originalsDir, 0755, true);

        $this->command->info('Téléchargement et création des photos de démonstration...');

        foreach ($this->catalogue as $data) {
            $filename = 'demo_' . $data['seed'] . '.jpg';
            $pubPath  = $imagesDir . '/' . $filename;
            $origPath = $originalsDir . '/' . $filename;

            // ── Téléchargement de l'image depuis Picsum ───────────────────────
            if (!file_exists($pubPath)) {
                $url = "https://picsum.photos/seed/{$data['seed']}/1920/1280";
                $this->command->line("  ↓ Téléchargement : {$data['description']}");

                $imageData = $this->download($url);

                if ($imageData === false) {
                    $this->command->warn("  ✗ Échec du téléchargement pour {$data['seed']} — photo ignorée.");
                    continue;
                }

                file_put_contents($pubPath, $imageData);
                file_put_contents($origPath, $imageData);
            } else {
                $this->command->line("  ✓ Déjà présente : {$filename}");
                if (!file_exists($origPath)) {
                    copy($pubPath, $origPath);
                }
            }

            // ── Filigrane ─────────────────────────────────────────────────────
            $wmName = 'wm_' . $filename;
            if (!file_exists($imagesDir . '/' . $wmName)) {
                $wmName = $watermark->apply($filename);
            }

            // ── Enregistrement en base (si pas déjà présent) ──────────────────
            if (Photo::where('nom_fichier', $filename)->exists()) {
                continue;
            }

            $catId = $categories[$data['categorie']] ?? null;
            if (!$catId) {
                $this->command->warn("  ✗ Catégorie '{$data['categorie']}' introuvable.");
                continue;
            }

            Photo::create([
                'description'           => $data['description'],
                'nom_fichier'           => $filename,
                'nom_fichier_filigrane' => $wmName,
                'prix'                  => $data['prix'],
                'id_categorie'          => $catId,
                'id_utilisateur'        => $photographe->id_utilisateur,
                'est_validee'           => true,   // validées d'office pour la démo
                'en_vente'              => true,
                'date_depot'            => now()->subDays(rand(1, 60)),
            ]);

            $this->command->info("  ✓ Photo créée : {$data['description']}");
        }

        $this->command->info('');
        $this->command->info(Photo::count() . ' photo(s) au total dans le catalogue.');
    }

    // ── Téléchargement avec timeout et user-agent ─────────────────────────────
    private function download(string $url): string|false
    {
        $context = stream_context_create([
            'http' => [
                'timeout'     => 30,
                'user_agent'  => 'Mozilla/5.0 PhotoForYou-Demo',
                'follow_location' => true,
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ]);

        return @file_get_contents($url, false, $context);
    }
}
