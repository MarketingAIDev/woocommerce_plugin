<?php

namespace Acelle\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Class ChatMessage
 * @package Acelle\Model
 *
 * @property integer id
 * @property string|Carbon|null created_at
 * @property string|Carbon|null updated_at
 * @property integer customer_id
 * @property Customer customer
 * @property integer user_id
 * @property User user
 * @property integer session_id
 * @property ChatSession session
 * @property boolean from_guest
 * @property string message
 * @property string message_type
 * @property string|null attachment_path
 * @property string|null attachment_mime_type
 * @property string|null attachment_size
 */
class ChatMessage extends Model
{
    public const ATTACHMENT_ACCEPTED_EXTENSIONS = 'doc,docx,odt,png,jpeg,jpg,pdf,txt';
    public const ATTACHMENT_MAX_SIZE_KB = 5120;

    const COLUMN_customer_id = 'customer_id';
    const COLUMN_user_id = 'user_id';
    const COLUMN_session_id = 'session_id';
    const COLUMN_from_guest = 'from_guest';
    const COLUMN_message = 'message';
    const COLUMN_attachment_path = 'attachment_path';
    const COLUMN_attachment_mime_type = 'attachment_mime_type';
    const COLUMN_attachment_size = 'attachment_size';
    const COLUMN_message_type = 'message_type';

    protected $fillable = [
        'customer_id',
        'user_id',
        'session_id',
        'from_guest',
        'message',
        'attachment_path',
        'attachment_mime_type',
        'attachment_size',
        'message_type',
    ];

    protected $appends = ['attachment_full_url'];

    public function getAttachmentFullUrlAttribute()
    {
        if (!$this->attachment_path)
            return "";
        return URL::asset($this->attachment_path);
    }

    static function validateAndStoreUserMessage($input_data, ChatSession $session)
    {
        $data = custom_validate($input_data, [
            'attachment' => 'file|max:' . self::ATTACHMENT_MAX_SIZE_KB . '|mimes:' . self::ATTACHMENT_ACCEPTED_EXTENSIONS,
            'message_type' => ['nullable', 'string', Rule::in(['bot_message', 'email_user_form', 'end_chat'])]
        ]);

        $message_type = $data['message_type'] ?? null;
        $attachment = $data['attachment'] ?? null;

        $message_required = "nullable";

        if (!$message_type && !$attachment) {
            $message_required = "required";
        }
        $extra_data = custom_validate($input_data, [
            'message' => "$message_required|string|max:500",
        ]);
        $data = array_merge($data, $extra_data);

        $model = new self();
        $model->session_id = $session->id;
        $model->customer_id = $session->customer->id;
        $model->user_id = $session->user->id;
        $model->from_guest = false;
        $model->message = $data["message"] ?? "";
        $model->message_type = $message_type ?? "";
        $session->messages()->save($model);


        $attachment = $data['attachment'] ?? null;
        if ($attachment) {
            // Validate the size for images separately.
            $extension = $attachment->extension();
            if (!in_array($extension, explode(',', self::ATTACHMENT_ACCEPTED_EXTENSIONS))) {
                throw ValidationException::withMessages([
                    'file' => "The file must be of type: " . self::ATTACHMENT_ACCEPTED_EXTENSIONS
                ]);
            }
            $path = $attachment->store('chat_images', 'public');
            $model->attachment_path = '/storage/' . $path;
            $model->attachment_mime_type = $attachment->getMimeType();
            $model->attachment_size = $attachment->getSize();
            $model->save();
        }

        $session->guest_unread_messages += 1;
        $session->agent_unread_messages = 0;
        $session->last_message_at = Carbon::now();
        $session->save();

        return $model;
    }

    public static function guestStoreRules()
    {
        return [
            'session_id' => 'required|integer',
            'secret_key' => 'required|string',
            'attachment' => 'file|max:' . self::ATTACHMENT_MAX_SIZE_KB . '|mimes:' . self::ATTACHMENT_ACCEPTED_EXTENSIONS,
            'message' => 'required_without:attachment|string',
        ];
    }

    public static function readRules()
    {
        return [
            'session_id' => 'required|string',
        ];
    }

    public static function guestReadRules()
    {
        return [
            'session_id' => 'required|integer',
            'secret_key' => 'required|string',
        ];
    }

    public static function paged_search($request)
    {
        return self::where('user_id', $request->user()->id)->paginate($request->per_page ?? 20);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function session()
    {
        return $this->belongsTo(ChatSession::class);
    }
}
