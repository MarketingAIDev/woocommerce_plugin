<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\ChatDepartment;
use Acelle\Model\User;
use Illuminate\Http\Request;

class ChatDepartmentController extends Controller
{
    public function listing(Request $request)
    {
        $request->merge(["user_id" => $request->user()->id]);

        $customers = ChatDepartment::paged_search($request);
        return response()->json([
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(ChatDepartment::rules());

        \Gate::authorize('create', ChatDepartment::class);

        /** @var User $user */
        $user = $request->user();
        $user->chat_departments()->create([
            'name' => $data['name']
        ]);
        return response('', 204);
    }

    public function update(Request $request, ChatDepartment $message)
    {
        $data = $request->validate(ChatDepartment::rules());

        \Gate::authorize('update', $message);

        $message->update([
            'name' => $data['name'],
        ]);
        return response('', 204);
    }

    public function destroy(ChatDepartment $message)
    {
        \Gate::authorize('update', $message);

        $message->delete();
        return response('', 204);
    }
}
