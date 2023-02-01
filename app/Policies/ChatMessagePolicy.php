<?php

namespace Acelle\Policies;

use Acelle\Model\User;
use Acelle\Model\ChatMessage;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChatMessagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the chatMessage.
     *
     * @param  \Acelle\Model\User  $user
     * @param  \Acelle\Model\ChatMessage  $chatMessage
     * @return mixed
     */
    public function view(User $user, ChatMessage $chatMessage)
    {
        //
    }

    /**
     * Determine whether the user can create chatMessages.
     *
     * @param  \Acelle\Model\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the chatMessage.
     *
     * @param  \Acelle\Model\User  $user
     * @param  \Acelle\Model\ChatMessage  $chatMessage
     * @return mixed
     */
    public function update(User $user, ChatMessage $chatMessage)
    {
        //
    }

    /**
     * Determine whether the user can delete the chatMessage.
     *
     * @param  \Acelle\Model\User  $user
     * @param  \Acelle\Model\ChatMessage  $chatMessage
     * @return mixed
     */
    public function delete(User $user, ChatMessage $chatMessage)
    {
        //
    }
}
