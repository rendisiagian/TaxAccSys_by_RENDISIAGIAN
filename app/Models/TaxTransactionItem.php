<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxTransactionItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function taxTransaction()
    {
        return $this->belongsTo(TaxTransaction::class);
    }
}
