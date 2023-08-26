<?php
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D Community Edition is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D Community Edition
 *
 * @author     HDVinnie <hdinnovations@protonmail.com>
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 */

namespace App\Models;

use App\Models\Scopes\ApprovedScope;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use Auditable;
    use HasFactory;

    public const PENDING = 0;
    public const APPROVED = 1;
    public const REJECTED = 2;

    /**
     * The Attributes That Are Mass Assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'status',
        'moderated_by',
        'moderated_at',
    ];

    protected $casts = [
        'moderated_at' => 'datetime',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected static function booted(): void
    {
        static::addGlobalScope(new ApprovedScope());
    }

    /**
     * Belongs To A User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, self>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Application Has Been Moderated By.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, self>
     */
    public function moderated(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /**
     * A Application Has Many Image Proofs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<ApplicationImageProof>
     */
    public function imageProofs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ApplicationImageProof::class);
    }

    /**
     * A Application Has Many URL Proofs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<ApplicationUrlProof>
     */
    public function urlProofs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ApplicationUrlProof::class);
    }
}
