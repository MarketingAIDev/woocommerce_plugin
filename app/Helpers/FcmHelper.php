<?php


namespace Acelle\Helpers;

use Acelle\Model\ChatMessage;
use Acelle\Model\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\FcmOptions;
use Kreait\Firebase\Messaging\Notification;

class FcmHelper
{
    public static function sendNotifications($user, ChatMessage $message)
    {
        // Safety checks
        /** @var User $user */
        if (!$user)
            return;

        $tokens = [];
        foreach ($user->fcm_tokens as $token) {
            $tokens[] = $token->fcm_token;
        }
        if (!count($tokens))
            return; // No tokens are there for the user.

        $message = CloudMessage::new()
            ->withNotification(Notification::create($message->session->getSessionName(), $message->message))
            ->withData([
                'click_action' => "FLUTTER_NOTIFICATION_CLICK",
                'session_id' => $message->session->id,
            ]);
        $messaging = app('firebase.messaging');
        $messaging->sendMulticast($message, $tokens);
    }
}
