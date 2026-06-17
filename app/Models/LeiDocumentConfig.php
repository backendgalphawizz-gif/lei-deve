<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiDocumentConfig extends Model
{
    protected $table = 'lei_document_config';

    protected $fillable = ['version_label', 'ledger_node', 'ledger_text'];
}
