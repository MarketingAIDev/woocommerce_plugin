<?php

namespace Acelle\Model;

use DOMDocument;
use DOMXPath;
use Acelle\Jobs\RunAutomationWithContext;
use Acelle\Library\Automation\DynamicWidgetConfig\ChatConfig;
use Acelle\Library\Automation\ShopifyAutomationContext;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;
use Stevebauman\Location\Facades\Location;

/**
 * Class ChatSession
 * @package Acelle\Model
 *
 * @property integer id
 * @property integer customer_id
 * @property Customer customer
 * @property integer user_id
 * @property User user
 * @property string name
 * @property string email
 * @property string secret_key
 * @property integer guest_unread_messages
 * @property integer agent_unread_messages
 * @property string|Carbon|null ended_at
 * @property integer|null feedback_rating
 * @property string feedback_message
 * @property string|null ip_address
 * @property string|Carbon|null last_active_at
 * @property string|Carbon|null last_message_at
 * @property integer subscriber_id
 * @property Subscriber subscriber
 * @property string|null location
 *
 * @property ChatMessage[] messages
 * @property ChatMessage[] messagesWithAttachments
 */
class ChatSession extends Model
{
    const COLUMN_id = 'id';
    const COLUMN_created_at = 'created_at';
    const COLUMN_updated_at = 'updated_at';
    const COLUMN_customer_id = 'customer_id';
    const COLUMN_user_id = 'user_id';
    const COLUMN_name = 'name';
    const COLUMN_email = 'email';
    const COLUMN_secret_key = 'secret_key';
    const COLUMN_guest_unread_messages = 'guest_unread_messages';
    const COLUMN_agent_unread_messages = 'agent_unread_messages';
    const COLUMN_ended_at = 'ended_at';
    const COLUMN_feedback_rating = 'feedback_rating';
    const COLUMN_feedback_message = 'feedback_message';
    const COLUMN_ip_address = 'ip_address';
    const COLUMN_last_active_at = 'last_active_at';
    const COLUMN_last_message_at = 'last_message_at';
    const COLUMN_subscriber_id = 'subscriber_id';
    const COLUMN_location = 'location';

    protected $dates = [
        self::COLUMN_ended_at,
        self::COLUMN_last_active_at,
        self::COLUMN_last_message_at,
    ];

    protected $hidden = [
        'user',
        'customer',
        'messages',
        'messagesWithAttachments',
    ];

    protected $casts = [
        self::COLUMN_guest_unread_messages => 'integer',
        self::COLUMN_agent_unread_messages => 'integer',
        self::COLUMN_location => 'array',
    ];

    function getSessionName(): string
    {
        return "#" . $this->id . " " . ($this->name ?: "Guest");
    }

    static function fixMissingFlags()
    {
        /** @var self[] $models */
        $models = self::query()->whereRaw('length(location) < 5')->limit(30000)->orderBy('created_at', 'desc')->get();
        foreach ($models as $model) {
            $location = Location::get($model->ip_address);
            print($model->ip_address . PHP_EOL);
            if (!empty($location)) {
                $model->location = $location;
                $model->save();
            }
        }
    }

    static function createSession(Customer $customer): self
    {
        $ip = request()->ip();
        $location = Location::get($ip);
        if (empty($location)) $location = "{}";

        $model = new self();
        $model->user_id = $customer->user->id;
        $model->customer_id = $customer->id;
        $model->name = "";
        $model->email = "";
        $model->secret_key = uniqid('', true);
        $model->guest_unread_messages = 0;
        $model->agent_unread_messages = 0;
        $model->ip_address = $ip;
        $model->last_active_at = Carbon::now();
        $model->location = $location;
        $model->save();

        $model->triggerFirstChatAutomation();
        return $model;
    }

    function getOrCreateSubscriber()
    {
        if ($this->subscriber)
            return $this->subscriber;

        $names = explode(" ", $this->name, 2);
        $subscriber = Subscriber::createSubscriber($this->customer->shopify_shop->mail_list,
            $this->email,
            $names[0] ?? "",
            $names[1] ?? ""
        );
        $this->subscriber_id = $subscriber->id;
        $this->save();
        return Subscriber::query()->find($this->subscriber_id);
    }

    function triggerChatEndedAutomations()
    {
        if (!$this->email) return;

        $customer = $this->customer;
        foreach ($customer->automation2s as $automation2) {
            if ($automation2->trigger_key == ShopifyAutomationContext::AUTOMATION_TRIGGER_CHAT_ENDED) {
                $context = new ShopifyAutomationContext();
                $context->chatSession = $this;
                $context->subscriber = $this->getOrCreateSubscriber();
                dispatch(new RunAutomationWithContext($automation2, $context));
            }
        }
    }

    function triggerFirstChatAutomation()
    {
        $count = $this->customer->chat_sessions()->count();
        if ($count > 1) return;

        $automation2s = Automation2::findByTriggerOnly(ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_FIRST_CHAT);
        foreach ($automation2s as $automation2) {
            $subscriber = $automation2->mailList->subscribers()->where('email', $this->customer->user->email)->first();
            if ($subscriber) {
                $context = new ShopifyAutomationContext();
                $context->chatSession = $this;
                $context->subscriber = $subscriber;
                dispatch(new RunAutomationWithContext($automation2, $context));
            }
        }
    }

    function triggerUnattendedChatAutomation()
    {
        $automation2s = Automation2::findByTriggerOnly(ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_UNATTENDED_CHAT);
        foreach ($automation2s as $automation2) {
            $subscriber = $automation2->mailList->subscribers()->where('email', $this->customer->user->email)->first();
            if ($subscriber) {
                $context = new ShopifyAutomationContext();
                $context->chatSession = $this;
                $context->subscriber = $subscriber;
                dispatch(new RunAutomationWithContext($automation2, $context));
            }
        }
    }

    static function checkForUnattendedChats()
    {
        $now = Carbon::now();
        $five_minutes_ago = $now->clone()->subMinutes(5);
        $six_minutes_ago = $now->clone()->subMinutes(5);
        /** @var self[] $chats */
        $chats = self::query()
            ->where(self::COLUMN_created_at, '>=', $six_minutes_ago)
            ->where(self::COLUMN_created_at, '<', $five_minutes_ago)
            ->get();

        foreach ($chats as $chat) {
            $messages = $chat->messages()->where(ChatMessage::COLUMN_from_guest, 0)->count();
            if ($messages == 0) {
                $chat->triggerUnattendedChatAutomation();
            }
        }
    }

    static function getChatReport(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'end_date' => 'nullable|date_format:Y-m-d',
            'start_date' => 'nullable|date_format:Y-m-d',
        ]);

        $end_date = Carbon::tomorrow();
        if (!empty($data['end_date']))
            $end_date = Carbon::parse($data['end_date'])->addDay();

        $start_date = $end_date->copy()->subDays(45);
        if (!empty($data['start_date']))
            $start_date = Carbon::parse($data['start_date']);
        if ($start_date > $end_date)
            throw ValidationException::withMessages([
                'start_date' => "Start date must not be greater than end date."
            ]);

        $diffinDays = $end_date->diffInDays($start_date);

        if ($diffinDays <= 45) {
            $mysql_format = '%Y-%m-%d';
            $php_format = 'Y-m-d';
        } else if ($diffinDays > 45 and $diffinDays <= 365) {
            $mysql_format = '%v week of %x';
            $php_format = 'W-Y';
        } else if ($diffinDays > 365 and $diffinDays <= 1095) {
            $mysql_format = '%Y-%m';
            $php_format = 'Y-m';
        } else {
            $mysql_format = '%Y';
            $php_format = 'Y';
        }

        $query = ChatSession::selectRaw("date_format(created_at, '${mysql_format}') formatted_date, date(min(created_at)) date, count(*) number_of_chats")
            ->where(self::COLUMN_customer_id, $customer->id)
            ->groupBy('formatted_date');

        $query->where('created_at', '<=', $end_date);
        $query->where('created_at', '>=', $start_date);
        // dd(Str::replaceArray('?', $query->getBindings(), $query->toSql()));
        $results = $query->get();
        $data = ChatSession::where(self::COLUMN_customer_id, $customer->id)
            ->where('created_at', '<=', $end_date)
            ->where('created_at', '>=', $start_date)
            ->where('guest_unread_messages', '>=', 1)
            ->get();
        $date_wise_results = [];
        foreach ($results as $result) {
            $date_wise_results[$result['formatted_date']] = $result;
        }

        $result_date = $start_date->copy();
        do {
            $formatted_date = $result_date->format($php_format);
            if ($diffinDays > 45 and $diffinDays <= 365) {
                $formatted_date = str_replace('-', ' week of ', $formatted_date);
            }
            if (empty($date_wise_results[$formatted_date]))
                $date_wise_results[$formatted_date] = [
                    'formatted_date' => $formatted_date,
                    'date' => $result_date->format("Y-m-d"),
                    'number_of_chats' => 0
                ];
            $result_date = $result_date->addDay();
        } while ($result_date < $end_date);
        usort($date_wise_results, function ($a, $b) {
            return ($a['formatted_date'] ?? '') <=> ($b['formatted_date'] ?? '');
        });

        $total_chats = 0;
        $total_chats_with_timeframe = 0;
        foreach ($date_wise_results as $result) {
            $total_chats += $result['number_of_chats'] ?? 0;
        }

        return [
            'total_chats' => $total_chats,
            'items' => array_values($date_wise_results)
        ];
    }

    static function validateAndEndSession(array $input_data)
    {
        $data = custom_validate($input_data, [
            'session_id' => 'required|string',
            'secret_key' => 'required|string',
            'feedback_rating' => 'required|integer|min:1|max:5',
            'feedback_message' => 'nullable|string|max:1000'
        ]);

        /** @var ChatSession $session */
        $session = ChatSession::where('id', $data['session_id'] ?? "")
            ->where('secret_key', $data['secret_key'] ?? "")
            ->first();
        if (!$session) {
            throw ValidationException::withMessages([
                'session_id' => 'Invalid session id'
            ]);
        }
        session(['chat_session_id' . $session->customer->uid => null]);

        $session->ended_at = Carbon::now();
        $session->feedback_message = $data['feedback_message'] ?? "";
        $session->feedback_rating = $data['feedback_rating'] ?? 0;
        $session->save();

        $session->triggerChatEndedAutomations();
    }

    protected $appends = ['shop_name', 'last_message'];

    public function getShopNameAttribute()
    {
        $customer = $this->customer;
        if (!$customer) return "";

        $shop = $customer->shopify_shop;
        if (!$shop) return "";

        return $shop->name;
    }

    /**
     * @return ChatMessage|null
     */
    public function getLastMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }

    /**
     * Find item by id.
     *
     * @return self
     */
    public static function findByid($uid)
    {
        return self::where('id', '=', $uid)->first();
    }

    public static function paged_search($request, $user_id, $skipEmpty = true, $liveOnly = false)
    {
        $query = ChatSession::query()->where(self::COLUMN_user_id, $user_id)
            ->with('user')
            ->orderBy(self::COLUMN_last_message_at, 'desc');
        if ($skipEmpty)
            $query->whereNotNull(self::COLUMN_last_message_at);
//            $query->has('messages');
        if ($liveOnly)
            $query->where(self::COLUMN_last_active_at, '>=', Carbon::now()->subRealMinutes(2));
        if (!empty($request->keyword))
            $query->where(function ($q) use ($request) {
                $q->orWhere(self::COLUMN_id, "LIKE", "%$request->keyword%")
                    ->orWhere(self::COLUMN_name, "LIKE", "%$request->keyword%")
                    ->orWhere(self::COLUMN_email, "LIKE", "%$request->keyword%");
            });
        return $query->paginate($request->per_page ?? 20);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, self::COLUMN_customer_id);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'session_id');
    }

    public function messagesWithAttachments()
    {
        return $this->hasMany(ChatMessage::class, 'session_id')->whereNotNull(ChatMessage::COLUMN_attachment_path);
    }

    function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class, self::COLUMN_subscriber_id);
    }

    /**
     * @param self $instance
     * @return string
     */
    static function getHtmlRepresentation($body, $customer, $subscriber_id, $email = null)
    {
        $noChats = false;
        $newHtml = $body;
        $html = new DOMDocument();
        //Throwing invalid Tag warnings
        @$html->loadHTML($body, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
        $xp = new DOMXPath($html);

        $e = $xp->query('//div[@class="chat-main-container row clearfix"]');
        if($email == null){
            $lastSession = ChatSession::query()
            ->where('customer_id', $customer->id)
            ->where('subscriber_id', $subscriber_id)->orderBy('id', "desc")
            ->latest()->take(1)->get();
        }else{
            $lastSession = ChatSession::query()
            ->where('customer_id', $customer->id)
            ->where('email', $email)->orderBy('id', "desc")
            ->latest()->take(1)->get();
        }
        if(count($lastSession) > 0){
            $chats = ChatMessage::query()
            ->where(ChatMessage::COLUMN_customer_id, $customer->id)
            ->where('session_id',$lastSession[0]->id)
            ->orderBy('id', "asc")->take(5)->get();
            if(count($e) > 0){
                $agentBubble = new DOMDocument();
                $youBubble = new DOMDocument();
                $a = $xp->evaluate('//div[@class="left-chat-bubble"]');
                $y = $xp->evaluate('//div[@class="right-chat-bubble"]');
                $agentBubble->appendChild($agentBubble->importNode($a[0]->parentNode->parentNode,true));
                $youBubble->appendChild($youBubble->importNode($y[0]->parentNode->parentNode,true));
    
                $agent = $agentBubble->saveHTML();
    
                $you = $youBubble->saveHTML();
                foreach($a as $aa){
                    $aa->parentNode->parentNode->removeChild($aa->parentNode);
                }
                foreach($y as $yy){
                    $yy->parentNode->parentNode->removeChild($yy->parentNode);
                }
                if(count($chats) > 0){
                    $bubbles = array();
                    foreach($xp->evaluate('//div[@class="chat-main-container row clearfix"]/text()') as $node){
                        $node->data = "";
                        $lastChat = "";
                        $msg = "";
                        foreach(($chats ?? []) as $chat){
                            if(($chat->from_guest && $lastChat == 'agent') || (!$chat->from_guest && $lastChat == 'you') || $msg == ""){
                                $msg .= $chat->message . '<br>';
                                if($chat->from_guest){
                                    $lastChat = "agent";
                                   $temp = $agent;
                                }else{
                                    $lastChat = "you";
                                   $temp = $you; 
                                }
                            }else{
                                if($chat->from_guest){
                                    $lastChat = "agent";
                                   $temp = $agent;
                                }else{
                                    $lastChat = "you";
                                   $temp = $you; 
                                }
                                $msg = substr($msg, 0, -4);
                                $newData = str_replace("Lorem ipsum dolor sit amet consectetur adipisicing elit. Nam sapiente fugiat suscipit dolore molestiae eos facilis sequi magni quidem voluptas eum ratione quaerat eligendi, consequatur itaque fuga cum inventore tempora?",$msg,$temp);
                                $newData = str_replace("1:22pm",date_format($chat->created_at,"j M ga"),$newData);
                                $node->data = $node->data . $newData; 
                                $msg = $chat->message . '<br>';
                            }
                        }
                        if($lastChat == 'you'){
                            $temp = $agent;
                        }else{
                            $temp = $you; 
                        }
                        $newData = str_replace("Lorem ipsum dolor sit amet consectetur adipisicing elit. Nam sapiente fugiat suscipit dolore molestiae eos facilis sequi magni quidem voluptas eum ratione quaerat eligendi, consequatur itaque fuga cum inventore tempora?",$msg,$temp);
                        $newData = str_replace("1:22pm",date_format($chat->created_at,"j M ga"),$newData);
                        $node->data = $node->data . $newData;
                    }
                }else{
                    $noChats = true;
                }
                $newHtml = html_entity_decode($html->saveHTML());
            }
        }else{
            $noChats = true;
        }
        if($noChats){
            foreach($xp->evaluate('//div[@class="chat-main-container rdw clearfix"]') as $node){
                foreach($xp->evaluate('div',$node) as $div){
                    $youBubble = $xp->evaluate('div[@class="right-chat-bubble"]',$div);
                    $div->parentNode->removeChild($div);
                    continue;
                }
                
            }
        }
        return $newHtml;
    }
    static function getSummary(Customer $customer): array
    {
        
        $now = Carbon::now();
        $h24_ago = $now->clone()->subDay();
        $h48_ago = $h24_ago->clone()->subDay();
        $sales_total = ShopifyOrder::getTotalRevenue($customer, null, null, "chat");
        $sales_total_last_24_hours = ShopifyOrder::getTotalRevenue($customer, $h24_ago, $now, "chat");
        $sales_total_last_48_to_24_hours = ShopifyOrder::getTotalRevenue($customer, $h48_ago, $h24_ago, "chat");
        $sales_total_change_last_24 = growth_rate($sales_total_last_24_hours, $sales_total_last_48_to_24_hours);

        $chats = Self::where(Self::COLUMN_customer_id, '=', $customer->id)->count();
        $chats_last_24_hours = Self::where(Self::COLUMN_customer_id, '=', $customer->id)
        ->where(self::COLUMN_created_at, '<=', $now)
        ->where(self::COLUMN_created_at, '>=', $h24_ago)
        ->count();

        $chats_last_48_to_24_hours = Self::where(Self::COLUMN_customer_id, '=', $customer->id)
        ->where(self::COLUMN_created_at, '<=', $h24_ago)
        ->where(self::COLUMN_created_at, '>=', $h48_ago)
        ->count();

        $chats_change_last_24 = growth_rate($chats_last_24_hours, $chats_last_48_to_24_hours);

        $missed = Self::where(Self::COLUMN_customer_id, '=', $customer->id)
        ->where(self::COLUMN_ended_at, '=', null)
        ->count();
        $missed_last_24_hours = Self::where(Self::COLUMN_customer_id, '=', $customer->id)
        ->where(self::COLUMN_created_at, '<=', $h24_ago)
        ->where(self::COLUMN_created_at, '>=', $h24_ago)
        ->where(self::COLUMN_ended_at, '=', null)
        ->count();

        $missed_last_48_to_24_hours = Self::where(Self::COLUMN_customer_id, '=', $customer->id)
        ->where(self::COLUMN_created_at, '<=', $h24_ago)
        ->where(self::COLUMN_created_at, '>=', $h48_ago)
        ->where(self::COLUMN_ended_at, '=', null)
        ->count();

        $missed_change_last_24 = growth_rate($missed_last_24_hours, $missed_last_48_to_24_hours);
        return [
            'sales_total' => $sales_total,
            'sales_total_last_24_hours' => $sales_total_last_24_hours,
            'sales_total_last_48_to_24_hours' => $sales_total_last_48_to_24_hours,
            'sales_total_change_last_24' => $sales_total_change_last_24,

            'chats' => $chats,
            'chats_last_24_hours' => $chats_last_24_hours,
            'chats_last_48_to_24_hours' => $chats_last_48_to_24_hours,
            'chats_change_last_24' => $chats_change_last_24,

            'missed' => $missed,
            'missed_last_24_hours' => $missed_last_24_hours,
            'missed_last_48_to_24_hours' => $missed_last_48_to_24_hours,
            'missed_change_last_24' => $missed_change_last_24,
        ];
    }
}
