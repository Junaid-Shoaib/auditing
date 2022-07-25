<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileManager extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'is_folder', 'company_id', 'year_id', 'parent_id', 'enabled'
    ];

    public function company(){
        return $this->belongsTo('App\Models\Company', 'company_id');
    }

    public function year(){
        return $this->belongsTo('App\Models\Year', 'year_id');
    }

    public function fileManger()
    {
        return $this->hasMany('App\Models\FileManager', 'parent_id');
    }
}
