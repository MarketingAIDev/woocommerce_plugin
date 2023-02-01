<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\ChatCannedMessage;
use Acelle\Model\User;
use Illuminate\Http\Request;

class ChatCannedMessageController extends Controller
{
    public function listing(Request $request)
    {
        $request->merge(["user_id" => $request->user()->id]);

        $customers = ChatCannedMessage::paged_search($request);
        return response()->json([
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(ChatCannedMessage::rules());

        \Gate::authorize('create', ChatCannedMessage::class);

        /** @var User $user */
        $user = $request->user();
        $user->chat_canned_messages()->create([
            'name' => $data['name'],
            'message' => $data['message'],
        ]);
        return response('', 204);
    }

    public function update(Request $request, ChatCannedMessage $message)
    {
        $data = $request->validate(ChatCannedMessage::rules());

        \Gate::authorize('update', $message);

        $message->update([
            'name' => $data['name'],
            'message' => $data['message'],
        ]);
        return response('', 204);
    }

    public function destroy(ChatCannedMessage $message)
    {
        \Gate::authorize('update', $message);

        $message->delete();
        return response('', 204);
    }
}
