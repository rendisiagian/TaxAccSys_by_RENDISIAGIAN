<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxTransaction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function relatedTransaction()
    {
        return $this->belongsTo(TaxTransaction::class, 'related_transaction_id');
    }

    public function relatedTaxes()
    {
        return $this->hasMany(TaxTransaction::class, 'related_transaction_id');
    }

    public function items()
    {
        return $this->hasMany(TaxTransactionItem::class);
    }
}
