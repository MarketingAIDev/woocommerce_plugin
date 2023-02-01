<?php

namespace Acelle\Policies;

use Acelle\Model\ChatCannedMessage;
use Acelle\Model\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChatCannedMessagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the chatCannedMessage.
     *
     * @param User $user
     * @param ChatCannedMessage $chatCannedMessage
     * @return mixed
     */
    public function view(User $user, ChatCannedMessage $chatCannedMessage)
    {
        return $chatCannedMessage->user->id === $user->id;
    }

    /**
     * Determine whether the user can create chatCannedMessages.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the chatCannedMessage.
     *
     * @param User $user
     * @param ChatCannedMessage $chatCannedMessage
     * @return mixed
     */
    public function update(User $user, ChatCannedMessage $chatCannedMessage)
    {
        return $chatCannedMessage->user->id === $user->id;
    }

    /**
     * Determine whether the user can delete the chatCannedMessage.
     *
     * @param User $user
     * @param ChatCannedMessage $chatCannedMessage
     * @return mixed
     */
    public function delete(User $user, ChatCannedMessage $chatCannedMessage)
    {
        return $chatCannedMessage->user->id === $user->id;
    }
}
