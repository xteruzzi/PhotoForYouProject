<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    protected $table      = 'categorie';
    protected $primaryKey = 'id_categorie';
    public $timestamps    = false;

    protected $fillable = ['libelle', 'description'];

    public function photos()
    {
        return $this->hasMany(Photo::class, 'id_categorie', 'id_categorie');
    }
}
