<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trial extends Model
{
    use HasFactory;

    protected $fillable = [
        'opn_debit', 'remain_debit', 'cls_debit',
        'opn_credit', 'remain_credit', 'cls_credit',
        'account_id', 'enabled', 'company_id'
    ];

    // public function accountGroup(){
    //     return $this->belongsTo('App\Models\AccountGroup', 'group_id');
    // }

    // public function company(){
    //     return $this->belongsTo('App\Models\Company', 'company_id');
    // }

    // public function entries()
    // {
    //     return $this->hasMany('App\Models\Entry', 'account_id');
    // }
}
