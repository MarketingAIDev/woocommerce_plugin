<?php

namespace Acelle\Http\Controllers;

use Acelle\Events\ChatReceivedForGuest;
use Acelle\Events\ChatReceivedForUser;
use Acelle\Helpers\FcmHelper;
use Acelle\Model\ChatMessage;
use Acelle\Model\ChatSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChatMessageController extends Controller
{
    public function listing(Request $request)
    {
        $request->merge(["user_id" => $request->user()->id]);

        $customers = ChatMessage::paged_search($request);
        return response()->json([
            'customers' => $customers,
        ]);
    }

    public function sessions(Request $request)
    {
        $sessions = ChatSession::paged_search($request, $request->user()->id);

        return response()->json([
            'sessions' => $sessions,
        ]);
    }

    public function liveSessions(Request $request)
    {
        $sessions = ChatSession::paged_search($request, $request->user()->id, false, true);

        return response()->json([
            'sessions' => $sessions,
        ]);
    }

    public function listGuest(Request $request)
    {
        $data = $request->validate(ChatMessage::guestReadRules());

        /** @var ChatSession $session */
        $session = ChatSession::where('id', $data['session_id'] ?? "")
            ->where('secret_key', $data['secret_key'] ?? "")
            ->first();
        if (!$session)
            throw ValidationException::withMessages([
                'session_id' => 'Invalid session id'
            ]);
        if($session->ended_at)
            throw ValidationException::withMessages([
                'session_ended' => 'Session ended'
            ]);

        $messages = $session->messages();
        if ($request->last_id) {
            $messages->where('id', '>', $request->last_id);
        }
        $messages->orderBy('id', 'asc');
        $session->guest_unread_messages = 0;
        $session->last_active_at = Carbon::now();
        $session->save();
        return response()->json([
            'messages' => $messages->get()
        ]);
    }

    public function list(Request $request)
    {
        $data = $request->validate(ChatMessage::readRules());

        /** @var ChatSession $session */
        $session = ChatSession::where('id', $data['session_id'] ?? "")
            ->where('user_id', $request->user()->id)
            ->first();
        if (!$session)
            throw ValidationException::withMessages([
                'session_id' => 'Invalid session id'
            ]);

        $messages = $session->messages();
        if ($request->last_id) {
            $messages->where('id', '>', $request->last_id);
        }
        $messages->orderBy('id', 'asc');
        $session->agent_unread_messages = 0;
        $session->save();
        return response()->json([
            'messages' => $messages->get()
        ]);
    }

    public function storeGuest(Request $request)
    {
        $data = $request->validate(ChatMessage::guestStoreRules());

        /** @var ChatSession $session */
        $session = ChatSession::where('id', $data['session_id'] ?? "")
            ->where('secret_key', $data['secret_key'] ?? "")
            ->first();
        if (!$session)
            throw ValidationException::withMessages([
                'session_id' => 'Invalid session id'
            ]);
        if($session->ended_at)
            throw ValidationException::withMessages([
                'session_ended' => 'Session ended'
            ]);

        /** @var ChatMessage $model */
        $model = $session->messages()->create([
            'session_id' => $session->id,
            'customer_id' => $session->customer->id,
            'user_id' => $session->user->id,
            'from_guest' => true,
            'message' => $data['message'] ?? "",
            'message_type' => ""
        ]);

        $attachment = $data['attachment'] ?? null;
        if ($attachment) {
            // Validate the size for images separately.
            $extension = $attachment->extension();
            if (!in_array($extension, explode(',', ChatMessage::ATTACHMENT_ACCEPTED_EXTENSIONS))) {
                throw ValidationException::withMessages([
                    'file' => "The file must be of type: " . ChatMessage::ATTACHMENT_ACCEPTED_EXTENSIONS
                ]);
            }
            $path = $attachment->store('chat_images', 'public');
            $model->attachment_path = '/storage/' . $path;
            $model->attachment_mime_type = $attachment->getMimeType();
            $model->attachment_size = $attachment->getSize();
            $model->save();
        }

        $session->agent_unread_messages += 1;
        $session->guest_unread_messages = 0;
        $session->last_active_at = Carbon::now();
        $session->last_message_at = Carbon::now();
        $session->save();

        FcmHelper::sendNotifications($session->user, $model);
        broadcast(new ChatReceivedForGuest($session, $model));
        broadcast(new ChatReceivedForUser($session->user, $session, $model));

        return response()->json(['chat_message' => $model]);
    }

    public function store(Request $request)
    {
        /** @var ChatSession $session */
        $session = ChatSession::where('id', $request['session_id'] ?? "")
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$session)
            throw ValidationException::withMessages([
                'session_id' => 'Invalid session id'
            ]);
        if($session->ended_at)
            throw ValidationException::withMessages([
                'session_ended' => 'Session ended'
            ]);

        $message = ChatMessage::validateAndStoreUserMessage($request->all(), $session);
        broadcast(new ChatReceivedForGuest($session, $message));
        broadcast(new ChatReceivedForUser($session->user, $session, $message));
        return response()->json(['chat_message' => $message]);
    }


    public function chatEnd(Request $request)
    {
        $data = $request->validate([
            'session_id' => 'required|integer',
        ]);

        /** @var ChatSession $session */
        $session = ChatSession::where('id', $data['session_id'] ?? "")
            ->where('user_id', $request->user()->id)
            ->first();
        if (!$session)
            throw ValidationException::withMessages([
                'session_id' => 'Invalid session id'
            ]);
        if($session->ended_at)
            throw ValidationException::withMessages([
                'session_ended' => 'Session ended'
            ]);

        /** @var ChatMessage $model */
        $model = $session->messages()->create([
            'session_id' => $session->id,
            'customer_id' => $session->customer->id,
            'user_id' => $session->user->id,
            'from_guest' => false,
            'message' => "",
            'message_type' => "request_chat_end"
        ]);

        $session->guest_unread_messages += 1;
        $session->agent_unread_messages = 0;
        $session->save();

        return response()->json(['message_id' => $model->id]);
    }

    function attachments(Request $request, string $session_id)
    {
        /** @var ChatSession $session */
        $session = ChatSession::where('id', $session_id)
            ->where('user_id', $request->user()->id)
            ->first();
        if (!$session) {
            throw new NotFoundHttpException();
        }

        return response()->json([
            'attachments' => $session->messagesWithAttachments()->paginate($request->per_page ?? 20)
        ]);
    }

    function updateSession(Request $request, string $session_id)
    {
        /** @var ChatSession $session */
        $session = ChatSession::where('id', $session_id)
            ->where('user_id', $request->user()->id)
            ->first();
        if (!$session)
            throw new NotFoundHttpException();
        if($session->ended_at)
            throw ValidationException::withMessages([
                'session_ended' => 'Session ended'
            ]);

        $data = $request->validate([
            "name" => "required|string|max:100",
            "email" => "required|email|max:100",
        ]);
        $session->name = $data["name"] ?? "";
        $session->email = $data["email"] ?? "";
        $session->save();

        return response()->json([
            'session' => $session,
            'message' => "Session updated successfully"
        ]);
    }
}
