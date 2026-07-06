<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A named menu group (Database Design, Section 4.5) — e.g. 'header',
 * 'footer'. Nothing beyond a name; all real structure lives in menu_items.
 */
class Menu extends Model
{
    use HasAuditColumns, HasFactory;

    protected $fillable = ['name'];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function topLevelItems(): HasMany
    {
        return $this->items()->whereNull('parent_id')->orderBy('order');
    }
}
