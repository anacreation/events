<?php
/**
 * Author: Xavier Au
 * Date: 18/4/2018
 * Time: 8:26 AM
 */

namespace Anacreation\Events\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Registration extends Model
{
    protected $fillable = [
        'participant_id',
        'event_id',
    ];

    // Relation

    public function participant(): Relation {
        return $this->belongsTo(Participant::class);
    }

    public function event(): Relation {
        return $this->belongsTo(Event::class);
    }

    // Public API
    public function cancel(): void {
        $this->delete();
    }
}