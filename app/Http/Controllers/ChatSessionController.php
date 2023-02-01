<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\ChatMessage;
use Acelle\Model\ChatSession;
use Acelle\Model\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ChatSessionController extends Controller
{
    /**
     * @param Request $request
     * @return Model
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_uid' => 'required|string',
            'name' => 'required|string',
            'email' => 'required|string'
        ]);

        $customer = Customer::findByUid($data['client_uid']);
        if (!$customer) {
            throw ValidationException::withMessages([
                'client_uid' => 'Invalid id'
            ]);
        }

        $chat_session = ChatSession::createSession($customer);

        session(['chat_session' . $customer->uid => $chat_session]);

        return $chat_session;
    }


    public function updateSession(Request $request)
    {
        $data = $request->validate(ChatMessage::guestReadRules());
        /** @var ChatSession $session */
        $session = ChatSession::where('id', $data['session_id'] ?? "")
            ->where('secret_key', $data['secret_key'] ?? "")
            ->first();
        if (!$session) {
            throw ValidationException::withMessages([
                'session_id' => 'Invalid session id'
            ]);
        }

        $data = $request->validate([
            'name' => 'required|string|max:200',
            'email' => 'required|email|max:200'
        ]);
        $session->email = $data['email'] ?? '';
        $session->name = $data['name'] ?? '';
        $session->save();
        $session->getOrCreateSubscriber();

        return response("", 201);
    }

    public function getSession(Request $request)
    {
        $data = $request->validate([
            'client_uid' => 'required|string'
        ]);

        $customer = Customer::findByUid($data['client_uid']);
        if (!$customer || !$customer->shopify_shop->enable_chat_script) {
            return response()->json([
                'message' => "Invalid Client UID"
            ], 404);
        }

        $session = null;
        $session_id = session('chat_session_id' . $customer->uid);
        if ($session_id) {
            $session = ChatSession::find($session_id);
            if ($session && $session->ended_at) {
                session(['chat_session_id' . $customer->uid => null]);
                $session = null;
            }
        }

        if (!$session) $session = ChatSession::createSession($customer);

        // Store to PHP session
        session(['chat_session_id' . $customer->uid => $session->id]);

        return $session;
    }

    function getSessionAndSettings(Request $request)
    {
        $data = $request->validate([
            'client_uid' => 'required|string'
        ]);

        $customer = Customer::findByUid($data['client_uid']);
        if (!$customer || !$customer->shopify_shop->enable_chat_script) {
            return response()->json([
                'message' => "Invalid Client UID"
            ], 404);
        }

        $settings = $customer->getChatSettings();

        // Update
        $extensions = $settings['file_sharing_extensions'] ?? "";
        $extensions_array = explode(',', $extensions);
        $settings['file_sharing_extensions'] = $extensions_array;

        // Bot image
        if (!empty($settings['bot_image']))
            $settings['bot_image'] = url($settings['bot_image']);

        $session = $this->getSession($request);
        $session->customer_uid = $session->customer->uid;

        return response()->json([
            'chat_session' => $session,
            'chat_settings' => $settings
        ]);
    }

    function endSession(Request $request)
    {
        ChatSession::validateAndEndSession($request->all());
        return response('', 204);
    }
}
