<?php
/**
 * Author: Xavier Au
 * Date: 18/4/2018
 * Time: 8:26 AM
 */

namespace Anacreation\Events\Models;


use Anacreation\Events\Exception\EnrollmentEndException;
use Anacreation\Events\Exception\EnrollmentNotYetStartException;
use Anacreation\Events\Exception\EnrollPastEventException;
use Anacreation\Events\Exception\NoVacancyException;
use Anacreation\Events\Models\Anacreation\Events\Exception\NotEventOrganiserException;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class Event extends Model
{
    use HasMediaTrait;

    protected $fillable = [
        'start_date',
        'end_date',
        'enrollment_start_date',
        'enrollment_end_date',
        'member_only',
        'is_active',
        'vacancy',
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'enrollment_start_date',
        'enrollment_end_date',
        'created_at',
        'updated_at',
    ];

    // config media collection

    public function registerMediaCollections() {
        $this->addMediaCollection('cover_image')
             ->singleFile();
    }

    // accessor
    public function getCoverImageUrlAttribute(): ?string {
        return $this->getFirstMedia("cover_image");
    }

    // Relation

    public function venue(): Relation {
        return $this->belongsTo(Venue::class);
    }

    public function registrations(): Relation {
        return $this->hasMany(Registration::class);
    }

    public function participants(): Relation {
        return $this->hasMany(Participant::class);
    }

    public function organiser(): Relation {
        return $this->belongsTo(User::class);
    }

    // public API

    /**
     * @param User $organiser
     * @return Collection
     */
    public static function organiserEvents(User $organiser): Collection {
        return static::whereOrganiserId($organiser->id)->get();
    }

    /**
     * @param Participant $participant
     * @return Registration
     * @throws EnrollPastEventException
     * @throws EnrollmentEndException
     * @throws EnrollmentNotYetStartException
     * @throws NoVacancyException
     */
    public function addParticipant(Participant $participant): Registration {

        $this->checkingForRegistration();

        /** @var Registration $registration */
        $registration = $this->_registration($participant);

        return $registration;
    }

    /**
     * @param User $user
     * @return Registration
     * @throws EnrollPastEventException
     * @throws EnrollmentEndException
     * @throws EnrollmentNotYetStartException
     * @throws NoVacancyException
     */
    public function addUser(User $user): Registration {

        $participant = Participant::createFromUser($user);

        return $this->addParticipant($participant);
    }

    public function hasVacancy(): bool {
        return $this->registrations()->count() < $this->vacancy;
    }

    public function checkVacancy(): bool {
        if (!$this->hasVacancy()) {
            throw new NoVacancyException("No vacancy for the event!");
        }

        return true;
    }

    /**
     * @return bool
     * @throws EnrollPastEventException
     * @throws EnrollmentEndException
     * @throws EnrollmentNotYetStartException
     * @throws NoVacancyException
     */
    public function checkingForRegistration(): bool {

        $this->isNotPastEvent();

        $this->inEnrollmentPeriod();

        $this->checkVacancy();

        return true;
    }

    /**
     * @return bool
     * @throws EnrollmentNotYetStartException
     * @throws EnrollmentEndException
     */
    public function inEnrollmentPeriod(): bool {
        if ($this->enrollment_start_date and $this->enrollment_end_date) {
            if ($this->enrollment_start_date->gt(Carbon::now())) {
                throw new EnrollmentNotYetStartException("Enrollment not yet start!");
            }
            if (Carbon::now()->gt($this->enrollment_end_date)) {
                throw new EnrollmentEndException("Enrollment already end!");
            }
        }

        return true;
    }

    /**
     * @return bool
     * @throws EnrollPastEventException
     */
    public function isNotPastEvent(): bool {
        if (Carbon::now()->gt($this->end_date)) {
            throw new EnrollPastEventException("Cannot enroll past event");
        };

        return true;
    }

    public function hasParticipant(Participant $participant): bool {
        return $this->registrations()->whereParticipantId($participant->id)
                    ->count() > 0;
    }

    /**
     * @param User        $organiser
     * @param Participant $participant
     * @return Registration
     * @throws EnrollPastEventException
     * @throws NotEventOrganiserException
     */
    public function organiserAddParticipant(
        User $organiser, Participant $participant
    ): Registration {
        if ($this->organiser->id === $organiser->id) {

            $this->isNotPastEvent();

            /** @var Registration $registration */
            $registration = $this->_registration($participant);

            return $registration;

        }
        throw new NotEventOrganiserException("User is not event organiser!");
    }


    /**
     * @param Participant $participant
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    private function _registration(Participant $participant) {
        $registration = $this->registrations()->create([
            'participant_id' => $participant->id
        ]);

        return $registration;
    }
}