<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A comercio using Vendi. Holds branding / white-label config in `settings`
 * (e.g. accent_color, logo). Domain data is linked via tenant_id + BelongsToTenant.
 */
class Tenant extends Model
{
    protected $fillable = ['name', 'slug', 'settings'];

    protected $casts = [
        'settings' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** Convenience accessor for the brand accent color (defaults to Vendi violet). */
    public function accentColor(): string
    {
        return $this->settings['accent_color'] ?? '#6C4DF2';
    }
}
