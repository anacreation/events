<?php
/**
 * Author: Xavier Au
 * Date: 18/4/2018
 * Time: 8:26 AM
 */

namespace Anacreation\Events\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Venue extends Model
{

    protected $fillable = [
        'name',
        'address'
    ];

    // Relation

    public function events(): Relation {
        return $this->hasMany(Event::class);
    }
}