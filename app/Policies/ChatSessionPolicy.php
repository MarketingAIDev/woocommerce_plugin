<?php

namespace Acelle\Policies;

use Acelle\Model\ChatSession;
use Acelle\Model\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChatSessionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the chatSession.
     *
     * @param User $user
     * @param ChatSession $chatSession
     * @return mixed
     */
    public function view(User $user, ChatSession $chatSession)
    {
        return true;
    }

    /**
     * Determine whether the user can create chatSessions.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the chatSession.
     *
     * @param User $user
     * @param ChatSession $chatSession
     * @return mixed
     */
    public function update(User $user, ChatSession $chatSession)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the chatSession.
     *
     * @param User $user
     * @param ChatSession $chatSession
     * @return mixed
     */
    public function delete(User $user, ChatSession $chatSession)
    {
        return $chatSession->user->id === $user->id;
    }
}
