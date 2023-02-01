<?php

namespace Acelle\Policies;

use Acelle\Model\ChatDepartment;
use Acelle\Model\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChatDepartmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the chatDepartment.
     *
     * @param User $user
     * @param ChatDepartment $chatDepartment
     * @return mixed
     */
    public function view(User $user, ChatDepartment $chatDepartment)
    {
        return $chatDepartment->user->id === $user->id;
    }

    /**
     * Determine whether the user can create chatDepartments.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the chatDepartment.
     *
     * @param User $user
     * @param ChatDepartment $chatDepartment
     * @return mixed
     */
    public function update(User $user, ChatDepartment $chatDepartment)
    {
        return $chatDepartment->user->id === $user->id;
    }

    /**
     * Determine whether the user can delete the chatDepartment.
     *
     * @param User $user
     * @param ChatDepartment $chatDepartment
     * @return mixed
     */
    public function delete(User $user, ChatDepartment $chatDepartment)
    {
        return $chatDepartment->user->id === $user->id;
    }
}
