<?php

namespace Acelle\Policies;

use Acelle\Model\Segment2;
use Acelle\Model\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class Segment2Policy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the segment2.
     *
     * @param User $user
     * @param Segment2 $segment2
     * @return mixed
     */
    public function view(User $user, Segment2 $segment2)
    {
        return $segment2->customer->user_id == $user->id;
    }

    /**
     * Determine whether the user can create segment2s.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the segment2.
     *
     * @param User $user
     * @param Segment2 $segment2
     * @return mixed
     */
    public function update(User $user, Segment2 $segment2)
    {
        return $segment2->customer->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the segment2.
     *
     * @param User $user
     * @param Segment2 $segment2
     * @return mixed
     */
    public function delete(User $user, Segment2 $segment2)
    {
        return $segment2->customer->user_id == $user->id;
    }
}
