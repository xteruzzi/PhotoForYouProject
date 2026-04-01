<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Categorie;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Comptes utilisateurs de démo ──────────────────────────────────────
        User::create([
            'nom'      => 'Admin',
            'prenom'   => 'SuperAdmin',
            'pseudo'   => 'superadmin',
            'email'    => 'admin@photoforyou.fr',
            'password' => Hash::make('Admin@1234'),
            'role'     => 'admin',
            'credits'  => 0,
            'actif'    => true,
        ]);

        User::create([
            'nom'      => 'Raverdi',
            'prenom'   => 'Flippo',
            'pseudo'   => 'flippo_photo',
            'email'    => 'flippo@photoforyou.fr',
            'password' => Hash::make('Photo@1234'),
            'role'     => 'photographe',
            'credits'  => 0,
            'actif'    => true,
        ]);

        User::create([
            'nom'      => 'Dupont',
            'prenom'   => 'Marie',
            'pseudo'   => 'marie_d',
            'email'    => 'marie@example.com',
            'password' => Hash::make('Client@1234'),
            'role'     => 'client',
            'credits'  => 20,
            'actif'    => true,
        ]);

        // ── Catégories ─────────────────────────────────────────────────────────
        $categories = [
            ['libelle' => 'Paysages',     'description' => 'Paysages naturels, couchers de soleil, panoramas'],
            ['libelle' => 'Portraits',    'description' => 'Portraits artistiques et professionnels'],
            ['libelle' => 'Architecture', 'description' => 'Bâtiments, monuments, villes'],
            ['libelle' => 'Nature',       'description' => 'Faune, flore, macro-photographie'],
            ['libelle' => 'Sport',        'description' => 'Action, compétitions, mouvements'],
            ['libelle' => 'Mode',         'description' => 'Fashion, studio, éditorial'],
            ['libelle' => 'Reportage',    'description' => 'Photojournalisme, événements, documentaire'],
            ['libelle' => 'Abstrait',     'description' => 'Art abstrait, longues expositions, créations'],
        ];

        foreach ($categories as $cat) {
            Categorie::create($cat);
        }
    }
}

