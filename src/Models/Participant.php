<?php
/**
 * Author: Xavier Au
 * Date: 18/4/2018
 * Time: 8:26 AM
 */

namespace Anacreation\Events\Models;


use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Participant extends Model
{
    protected $fillable = [
        'user_id'
    ];

    // Relation

    public function event(): Relation {
        return $this->belongsTo(Event::class);
    }

    // Public API

    /**
     * @param \App\User $user
     * @return \Anacreation\Events\Models\Participant
     */
    public static function createFromUser(User $user): Participant {
        $newParticipant = new Participant();
        $newParticipant->user_id = $user->id;
        $newParticipant->save();

        return $newParticipant;
    }
}