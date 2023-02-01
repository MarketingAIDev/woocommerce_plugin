<?php

namespace Acelle\Model;

use Acelle\Helpers\EmailImageHelper;
use Acelle\Library\Automation\DynamicWidgetConfig\CartConfig;
use Acelle\Library\Automation\DynamicWidgetConfig\OrderConfig;
use Acelle\Library\Automation\DynamicWidgetConfig\ProductConfig;
use Acelle\Library\Automation\DynamicWidgetConfig\ChatConfig;
use Acelle\Library\Automation\DynamicWidgetConfig\Products3Config;
use Acelle\Library\Automation\ShopifyAutomationContext;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use ZipArchive;
use KubAT\PhpSimple\HtmlDomParser;
use Acelle\Library\ExtendedSwiftMessage;
use Acelle\Library\Tool;
use Acelle\Library\Rss;
use Illuminate\Http\Request;
use Acelle\Library\StringHelper;
use Acelle\Library\Log as MailLog;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Acelle\Jobs\DeliverEmail;

/**
 * Class Email
 * @package Acelle\Model
 * @property integer id
 * @property string uid
 * @property integer automation2_id
 * @property Automation2 automation
 * @property string subject
 * @property string from
 * @property string from_name
 * @property string reply_to
 * @property string content
 * @property string|Carbon created_at
 * @property string|Carbon updated_at
 * @property bool sign_dkim
 * @property bool track_open
 * @property bool track_click
 * @property string action_id
 * @property string plain
 * @property integer tracking_domain_id
 * @property TrackingDomain trackingDomain
 *
 * @property Attachment[] attachments
 * @property EmailLink[] emailLinks
 * @property TrackingLog[] trackingLogs
 * @property DeliveryAttempt[] deliveryAttempts
 */
class Email extends Model
{
    const COLUMN_id = 'id';
    const COLUMN_uid = 'uid';
    const COLUMN_automation2_id = 'automation2_id';
    const COLUMN_subject = 'subject';
    const COLUMN_from = 'from';
    const COLUMN_from_name = 'from_name';
    const COLUMN_reply_to = 'reply_to';
    const COLUMN_content = 'content';
    const COLUMN_created_at = 'created_at';
    const COLUMN_updated_at = 'updated_at';
    const COLUMN_sign_dkim = 'sign_dkim';
    const COLUMN_track_open = 'track_open';
    const COLUMN_track_click = 'track_click';
    const COLUMN_action_id = 'action_id';
    const COLUMN_plain = 'plain';
    const COLUMN_tracking_domain_id = 'tracking_domain_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subject', 'from', 'from_name', 'reply_to', 'sign_dkim', 'track_open', 'track_click', 'action_id',
    ];

    // Cached HTML content
    protected $parsedContent = null;

    // region Relations

    /**
     * Association with mailList through mail_list_id column.
     */
    public function automation()
    {
        return $this->belongsTo(Automation2::class, 'automation2_id');
    }

    /**
     * Association with attachments.
     */
    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * Association with email links.
     */
    public function emailLinks()
    {
        return $this->hasMany(EmailLink::class);
    }

    /**
     * Association with open logs.
     */
    public function trackingLogs()
    {
        return $this->hasMany(TrackingLog::class);
    }

    public function deliveryAttempts()
    {
        return $this->hasMany(DeliveryAttempt::class);
    }

    /**
     * Get email's associated tracking domain.
     */
    public function trackingDomain()
    {
        return $this->belongsTo(TrackingDomain::class, 'tracking_domain_id');
    }

    // endregion

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating automation.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            $item->uid = $uid;
        });

        static::deleted(function ($item) {
            if (is_dir($item->getEmailStoragePath())) {
                File::deleteDirectory($item->getEmailStoragePath());
            }
        });
    }

    /**
     * @return self
     */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    /**
     * Create automation rules.
     *
     * @return array
     */
    public function rules($request = null)
    {
        $rules = [
            'subject' => 'required',
            'from' => 'required|email',
            'from_name' => 'required',
        ];

        // tracking domain
        if (isset($request) && $request->custom_tracking_domain) {
            $rules['tracking_domain_uid'] = 'required';
        }

        return $rules;
    }

    /**
     * Get public campaign upload uri.
     */
    public function getUploadUri()
    {
        return 'app/email/template_' . $this->uid;
    }

    /**
     * Get public campaign upload dir.
     */
    public function getUploadDir()
    {
        return storage_path('app/email/template_' . $this->uid);
    }

    /**
     * Create template from layout.
     *
     * All availabel template tags
     */
    public function addTemplateFromLayout($layout)
    {
        $sDir = database_path('layouts/' . $layout);
        $this->loadFromDirectory($sDir);
    }

    /**
     * Get builder templates.
     *
     * @return mixed
     */
    public function getBuilderTemplates($customer)
    {
        $result = [];

        // Gallery
        $templates = Template::where('customer_id', '=', null)
            ->orWhere('customer_id', '=', $customer->id)
            ->orderBy('customer_id')
            ->get();

        foreach ($templates as $template) {
            $result[] = [
                'name' => $template->name,
                'thumbnail' => $template->getThumbUrl(),
            ];
        }

        return $result;
    }

    /* ABSOLUTE NON-SENSE CODE BY NITISH. NOT USED ANYMORE. */
    static function getEmailReport(Request $request, Customer $customer)
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

        $mail_list = MailList::where('customer_id', $customer->id)->get();
        $email_prev = '';
        foreach ($mail_list as $email) {

            if ($email['from_email'] != $email_prev) {
                $query[] = Email::selectRaw("date_format(created_at, '${mysql_format}') formatted_date, date(min(created_at)) date, count(*) number_of_emails")
                    ->where('from', $email['from_email'])
                    ->where('created_at', '<=', $end_date)
                    ->where('created_at', '>=', $start_date)
                    ->groupBy('formatted_date')->get();
            }
            $email_prev = $email['from_email'];

        }
        $results = $query;
        $date_wise_results = [];
        foreach ($results[0] as $result) {
            $date_wise_results[$result['formatted_date']] = $result;
        }
        $result_date = $start_date->copy();
        do {
            $formatted_date = $result_date->format($php_format);
            if ($diffinDays > 45 and $diffinDays <= 365) {
                $formatted_date = str_replace('-', ' week of ', $formatted_date);
            }
            if (empty($date_wise_results[$formatted_date])) {
                $date_wise_results[$formatted_date] = [
                    'formatted_date' => $formatted_date,
                    'date' => $result_date->format("Y-m-d"),
                    'number_of_emails' => 0
                ];
            }
            $result_date = $result_date->addDay();
        } while ($result_date < $end_date);
        usort($date_wise_results, function ($a, $b) {
            return ($a['formatted_date'] ?? '') <=> ($b['formatted_date'] ?? '');
        });

        $from_emails = MailList::where('customer_id', $customer->id)->get();
        $email_prev = '';
        foreach ($from_emails as $email) {
            if ($email['from_email'] != $email_prev) {
                $emails[] = Email::where('from', $email['from_email'])->get();
            }
            $email_prev = $email['from_email'];
        }
        $delivered_email = 0;
        $opened_email = 0;
        $clicked_email = 0;
        // dd($emails[0]);
        foreach ($emails[0] as $email) {
            if ($email['track_click'] == 1) {
                $delivered_email = $delivered_email + 1;
                $opened_email = $opened_email + 1;
                $clicked_email = $clicked_email + 1;
            } elseif ($email['track_open'] == 1) {
                $opened_email = $opened_email + 1;
                $delivered_email = $delivered_email + 1;
            } elseif ($email['sign_dkim'] == 1) {
                $delivered_email = $delivered_email + 1;
            }
        }

        return [
            'total_emails' => count($emails[0]),
            'delivered_email' => $delivered_email,
            'opened_email' => $opened_email,
            'clicked_email' => $clicked_email,
            // 'items' => array_values($date_wise_results)
        ];

    }

    public function saveImages()
    {
        $content = $this->content;
        // find all img tags in html content
        preg_match_all('/(<img[^>]+src=)([\'"])(?<src>.*?)(\2)/i', $content, $matches);
        $srcs = array_unique($matches['src']);
        $replacements = [];

        foreach ($srcs as $src) {
            if (empty(trim($src))) {
                continue;
            }
            $asset_url_root = route('email_assets', ['uid' => $this->uid, 'name' => ""], false);
            $storage_folder = $this->getStoragePath();
            $new_path = EmailImageHelper::saveImage($asset_url_root, $storage_folder, $src);
            $replacements[] = $new_path;

            try {
                $content = preg_replace('/(<img[^>]+src=)([\'"])(' . preg_quote($src, '/') . ')(\2)/', '$1$2' . $new_path . '$4', $content);
            } catch (\ErrorException $ex) {
                // preg_replace failed
                // use the old and non-safe way!!!!
                $content = str_replace($src, $new_path, $content);
            }

        }

        $this->content = $content;
    }

    /**
     * Upload attachment.
     * @param UploadedFile $file
     */
    public function uploadAttachment($file)
    {
        /** @var Attachment $att */
        $att = $this->attachments()->make();
        $att->size = $file->getSize();
        $att->name = $file->getClientOriginalName();

        $file->move(
            $this->getAttachmentPath(),
            $att->name
        );

        $att->file = $this->getAttachmentPath($att->name);
        $att->save();

        return $att;
    }

    /**
     * Get attachment path.
     */
    public function getAttachmentPath($sub = '')
    {
        return $this->getEmailStoragePath(join_paths('attachment', $sub));
    }

    /**
     * Copy new template.
     */
    public function copyFromTemplate($template)
    {
        Tool::xdelete($this->getStoragePath());

        Tool::xcopy($template->getStoragePath(), $this->getStoragePath());

        // replace image url
        $this->content = $template->content;
        $template_storage = route('template_assets', ['uid' => $template->uid, 'name' => ""], false);
        $campaign_storage = route('email_assets', ['uid' => $this->uid, 'name' => ""], false);
        $this->content = str_replace($template_storage, $campaign_storage, $this->content);
        $this->replaceInternalTags();
        $this->save();
    }

    /**
     * Get thumb.
     */
    public function getThumbName()
    {
        // find index
        $names = array('thumbnail.png', 'thumbnail.jpg', 'thumbnail.png', 'thumb.jpg', 'thumb.png');
        foreach ($names as $name) {
            if (is_file($file = $this->getStoragePath($name))) {
                return $name;
            }
        }

        return;
    }

    /**
     * Get thumb.
     */
    public function getThumb()
    {
        // find index
        if ($this->getThumbName()) {
            return $this->getStoragePath($this->getThumbName());
        }

        return;
    }

    /**
     * Get thumb.
     */
    public function getThumbUrl()
    {
        if ($this->getThumbName()) {
            return route('email_assets', ['uid' => $this->uid, 'name' => $this->getThumbName()]);
        } else {
            return url('assets/images/placeholder.jpg');
        }
    }

    /**
     * Load from directory.
     */
    public function loadFromDirectory($tmp_path)
    {
        // remove current folder
        // exec("rm -r {$tmp_path}");
        Tool::xdelete($this->getStoragePath());

        // try to find the main file, index.html | index.html | file_name.html | ...
        $main_file = null;
        $sub_path = '';
        $possible_main_file_names = array('index.html', 'index.htm');

        $possible_main_file_names = array('index.html', 'index.htm');
        foreach ($possible_main_file_names as $name) {
            if (is_file($file = $tmp_path . '/' . $name)) {
                $main_file = $file;
                break;
            }
            $dirs = array_filter(glob($tmp_path . '/' . '*'), 'is_dir');
            foreach ($dirs as $sub) {
                if (is_file($file = $sub . '/' . $name)) {
                    $main_file = $file;
                    $sub_path = explode('/', $sub)[count(explode('/', $sub)) - 1] . '/';
                    break;
                }
            }
        }
        // try to find first htm|html file
        if ($main_file === null) {
            $objects = scandir($tmp_path);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (!is_dir($tmp_path . '/' . $object)) {
                        if (preg_match('/\.html?$/i', $object)) {
                            $main_file = $tmp_path . '/' . $object;
                            break;
                        }
                    }
                }
            }
            $dirs = array_filter(glob($tmp_path . '/' . '*'), 'is_dir');
            foreach ($dirs as $sub) {
                $objects = scandir($sub);
                foreach ($objects as $object) {
                    if ($object != '.' && $object != '..') {
                        if (!is_dir($sub . '/' . $object)) {
                            if (preg_match('/\.html?$/i', $object)) {
                                $main_file = $sub . '/' . $object;
                                $sub_path = explode('/', $sub)[count(explode('/', $sub)) - 1] . '/';
                                break;
                            }
                        }
                    }
                }
            }
        }

        // // main file not found
        // if ($main_file === null) {
        //     $validator->errors()->add('file', 'Cannot find index file (index.html) in the template folder');
        //     throw new ValidationException($validator);
        // }

        // read main file content
        $html_content = trim(file_get_contents($main_file));
        $this->content = $html_content;
        $this->save();

        // upload path
        $upload_path = $this->getStoragePath();

        // copy all folder to public path
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        // exec("cp -r {$tmp_path}/* {$public_upload_path}/");
        Tool::xcopy($tmp_path, $upload_path);
    }

    /**
     * Get public campaign upload dir.
     */
    public function getEmailStoragePath($sub = '')
    {
        $base = storage_path(join_paths('app/users/', $this->automation->customer->uid, '/emails/', $this->uid));
        if (!\File::exists($base)) {
            \File::makeDirectory($base, 0777, true, true);
        }
        return join_paths($base, $sub);
    }

    /**
     * Get public campaign upload dir.
     */
    public function getStoragePath($sub = '')
    {
        $base = $this->getEmailStoragePath('content');
        if (!\File::exists($base)) {
            \File::makeDirectory($base, 0777, true, true);
        }
        return join_paths($base, $sub);
    }

    /**
     * Upload a template.
     */
    public function uploadTemplate($request)
    {
        $rules = array(
            'file' => 'required|mimetypes:application/zip,application/octet-stream,application/x-zip-compressed,multipart/x-zip',
        );

        $validator = Validator::make($request->all(), $rules, [
            'file.mimetypes' => 'Input must be a valid .zip file',
        ]);

        if ($validator->fails()) {
            return [false, $validator];
        }

        // move file to temp place
        $tmp_path = storage_path('tmp/uploaded_template_' . $this->uid . '_' . time());
        $file_name = $request->file('file')->getClientOriginalName();
        $request->file('file')->move($tmp_path, $file_name);
        $tmp_zip = join_paths($tmp_path, $file_name);

        // read zip file check if zip archive invalid
        $zip = new ZipArchive();
        if ($zip->open($tmp_zip, ZipArchive::CREATE) !== true) {
            // @todo hack
            // $validator = Validator::make([], []); // Empty data and rules fields
            $validator->errors()->add('file', 'Cannot open .zip file');

            return [false, $validator];
        }

        // unzip template archive and remove zip file
        $zip->extractTo($tmp_path);
        $zip->close();
        unlink($tmp_zip);

        $this->loadFromDirectory($tmp_path);

        // remove tmp folder
        // exec("rm -r {$tmp_path}");
        Tool::xdelete($tmp_path);

        return [true, $validator];
    }

    /**
     * transform URL.
     */
    public function transform($useTrackingHost = false)
    {
        $this->content = $this->transformWithoutUpdate($this->content, $useTrackingHost);
        return $this->content;
    }

    /**
     * transform URL.
     */
    public function transformWithoutUpdate($html, $useTrackingHost = false)
    {
        // Notice that there are 2 types of assets that need to untransform
        // #1 Campaign relative URL
        // #2 User assets (uploaded by file manager) absolute URL
        // 
        $host = config('app.url'); // Default host

        if ($useTrackingHost) {
            // Replace #2
            $html = str_replace($host, $this->getTrackingHost(), $html);
            $host = $this->getTrackingHost();
        }

        // Replace #1
        $path = route('email_assets', ['uid' => $this->uid, 'name' => ""], false);
        $html = Tool::replaceTemplateUrl($html, $host, $path);

        // By the way, fix <html>
        $html = tinyDocTypeTransform($html);

        return $html;
    }

    /**
     * transform URL.
     */
    public function untransform()
    {
        // Notice that there are 2 types of assets that need to untransform
        // #1 Email relative URL
        // #2 User assets (uploaded by file manager) absolute URL
        // 
        // and only #1 will be untransformed while #2 is kept as is

        // Replace relative URL with campaign URL
        $this->content = str_replace(
            route('email_assets', ['uid' => $this->uid, 'name' => ""]) . '/',
            '',
            tinyDocTypeUntransform($this->content)
        );
    }

    /**
     * Render email content for BUILDER EDIT
     */
    public function render()
    {
        $body = $this->transformWithoutUpdate($this->content, $useTrackingHost = false);
        return $body;
    }

    /**
     * Upload asset.
     */
    public function uploadAsset($file)
    {
        return $this->automation->customer->user->uploadAsset($file);
    }

    /**
     * Find and update email links.
     */
    public function updateLinks()
    {
        if (!$this->content) {
            return false;
        }

        $links = [];

        // find all links from contents
        $document = HtmlDomParser::str_get_html($this->content);
        foreach ($document->find('a') as $element) {
            if (preg_match('/^http/', $element->href) != 0) {
                $links[] = trim($element->href);
            }
        }

        // delete al bold links
        $this->emailLinks()->whereNotIn('link', $links)->delete();

        foreach ($links as $link) {
            $exist = $this->emailLinks()->where('link', '=', $link)->count();

            if (!$exist) {
                $this->emailLinks()->create([
                    'link' => $link,
                ]);
            }
        }
    }

    public function queueDeliverTo($subscriber, $triggerId, ShopifyAutomationContext $context)
    {
        $deliveryAttempt = $this->deliveryAttempts()->create([
            'subscriber_id' => $subscriber->id,
            'auto_trigger_id' => $triggerId,
            // 'email_id' already included
        ]);

        dispatch(new DeliverEmail(
            $this,
            $subscriber,
            $triggerId,
            $context,
            $deliveryAttempt->id,
            get_class($deliveryAttempt)
        ));

        return $deliveryAttempt;
    }

    // @note: complicated dependencies
    // It is just fine, we can think Email as an object depends on User/Customer
    /**
     * @param Subscriber $subscriber
     * @param string $triggerId
     * @param ShopifyAutomationContext $context
     */
    public function deliverTo($subscriber, $triggerId, ShopifyAutomationContext $context)
    {
        // @todo: code smell here, violation of Demeter Law
        // @todo: performance
        $quota_check_count = 0;
        while ($this->automation->customer->overQuota()) {
            $quota_check_count++;
            if($quota_check_count > 60)
                throw new \Exception('Customer has reached sending limit');
            MailLog::warning(sprintf('Email `%s` (%s) to `%s` halted, user exceeds sending limit', $this->subject, $this->uid, $subscriber->email));
            sleep(60);
        }

        $server = $subscriber->mailList->pickSendingServer();

        MailLog::info("Sending to subscriber `{$subscriber->email}`");

        list($message, $msgId) = $this->prepare($subscriber, $context);
        MailLog::info("Prepared mail for `{$subscriber->email}`");

        $sent = $server->send($message);

        // additional log
        MailLog::info("Sent to subscriber `{$subscriber->email}`");
        $this->trackMessage($sent, $subscriber, $server, $msgId, $triggerId);
    }

    /**
     * Prepare the email content using Swift Mailer.
     *
     * @input object subscriber
     * @input object sending server
     *
     * @param $subscriber
     * @param ShopifyAutomationContext|null $context
     * @return array
     */
    public function prepare($subscriber, ShopifyAutomationContext $context = null)
    {
        // build the message
        $customHeaders = $this->getCustomHeaders($subscriber, $this);
        $msgId = $customHeaders['X-Emailwish-Message-Id'];

        $message = new ExtendedSwiftMessage();
        $message->setId($msgId);

        // fixed: HTML type only
        $message->setContentType('text/html; charset=utf-8');

        foreach ($customHeaders as $key => $value) {
            $message->getHeaders()->addTextHeader($key, $value);
        }

        // @TODO for AWS, setting returnPath requires verified domain or email address
        $server = $subscriber->mailList->pickSendingServer();
        if ($server->allowCustomReturnPath()) {
            $returnPath = $server->getVerp($subscriber->email);
            if ($returnPath) {
                $message->setReturnPath($returnPath);
            }
        }
        $message->setSubject($this->getSubject($subscriber, $msgId, $context));
        $message->setFrom(array($this->from => $this->from_name));
        $message->setTo($subscriber->email);
        $message->setReplyTo($this->reply_to);
        $message->setEncoder(new \Swift_Mime_ContentEncoder_PlainContentEncoder('8bit'));
        $html = $this->getHtmlContent($subscriber, $msgId, $server, $context);
        $message->addPart($this->getPlainContent($html), 'text/plain');
        $message->addPart($html, 'text/html');

        //if ($this->sign_dkim) {
        $message = $this->sign($message, $server);
        //}

        foreach ($this->attachments as $file) {
            $attachment = \Swift_Attachment::fromPath($file->file);
            $message->attach($attachment);
            // This is used by certain delivery services like ElasticEmail
            $message->extAttachments[] = ['path' => $file->file, 'type' => $attachment->getContentType()];
        }

        return array($message, $msgId);
    }

    /**
     * Build Email Custom Headers.
     *
     * @return array list of custom headers
     */
    public function getCustomHeaders($subscriber, $server)
    {
        $msgId = StringHelper::generateMessageId(StringHelper::getDomainFromEmail($this->from));

        return array(
            'X-Emailwish-Campaign-Id' => $this->uid,
            'X-Emailwish-Subscriber-Id' => $subscriber->uid,
            'X-Emailwish-Customer-Id' => $this->automation->customer->uid,
            'X-Emailwish-Message-Id' => $msgId,
            'X-Emailwish-Sending-Server-Id' => $server->uid,
            'List-Unsubscribe' => '<' . $this->generateUnsubscribeUrl($msgId, $subscriber) . '>',
            'List-Unsubscribe-Post'=> 'List-Unsubscribe=One-Click',
            'Precedence' => 'bulk',
        );
    }

    public function generateUnsubscribeUrl($msgId, $subscriber)
    {
        // OPTION 1: immediately opt out
        $path = route('unsubscribeUrl', ['message_id' => StringHelper::base64UrlEncode($msgId)], false);

        // OPTION 2: unsubscribe form
        $path = route('unsubscribeForm', ['list_uid' => $subscriber->mailList->uid, 'code' => $subscriber->getSecurityToken('unsubscribe'), 'uid' => $subscriber->uid], false);

        return $this->buildPublicUrl($path);
    }

    /**
     * Log delivery message, used for later tracking.
     */
    public function trackMessage($response, $subscriber, $server, $msgId, $triggerId = null)
    {

        // @todo: customerneedcheck
        $params = array_merge(array(
            // 'email_id' => $this->id,
            'message_id' => $msgId,
            'subscriber_id' => $subscriber->id,
            'sending_server_id' => $server->id,
            'customer_id' => $this->automation->customer->id,
            'auto_trigger_id' => $triggerId,
        ), $response);

        if (!isset($params['runtime_message_id'])) {
            $params['runtime_message_id'] = $msgId;
        }

        // create tracking log for message
        $this->trackingLogs()->create($params);

        // increment customer quota usage
        $this->automation->customer->countUsage($msgId);
        $server->countUsage();
    }

    /**
     * Get tagged Subject.
     *
     * @return string
     */
    public function getSubject($subscriber, $msgId, ShopifyAutomationContext $context = null)
    {
        return $this->tagMessage($this->subject, $subscriber, $msgId, null, $context);
    }

    public function buildPublicUrl($path)
    {
        return join_url($this->getTrackingHost(), $path);
    }

    public function tagMessage($message, $subscriber, $msgId, $server = null, ShopifyAutomationContext $context = null)
    {
        if (!is_null($server) && $server->isElasticEmailServer()) {
            $message = $server->addUnsubscribeUrl($message);
        }
        $sig = $this->automation->customer->signature;

        $tags = array(
            'IN_WEBSITE' => $sig ? $sig->website : "",
            'IN_ADDRESS' => $this->automation->customer->getContact()->getAddress(),
            'IN_BRANDNAME' => $this->automation->customer->shopify_shop->name,
            'IN_EMAIL' => $this->automation->customer->user->email,
            'IN_FIRSTNAME' => $this->automation->customer->user->first_name,
            'IN_FULLNAME' => $sig ? $sig->full_name : "",
            'IN_POSITION' => $sig ? $sig->designation : "",
            'IN_PHONE' => $sig ? $sig->phone : "",
            'IN_FACEBOOK' => $sig ? $sig->facebook : "",
            'IN_INSTAGRAM' => $sig ? $sig->instagram : "",
            'IN_LINKEDIN' => $sig ? $sig->linkedin : "",
            'IN_TWITTER' => $sig ? $sig->twitter : "",
            'IN_SKYPE' => $sig ? $sig->skype : "",
            'IN_YOUTUBE' => $sig ? $sig->youtube : "",
            'IN_TIKTOK' => $sig ? $sig->tiktok : "",
            'IN_PINTEREST' => $sig ? $sig->pinterest : "",
            'IN_LOGO_URL' => $sig && $sig->logo_url ? $sig->logo_url : "/cb/assets/minimalist-blocks/images/logoplaceholder.png",
            'IN_THEME_FONT_FAMILY' => $shop->theme['font_family'] ?? 'Roboto',
            'IN_THEME_PRIMARY_BACKGROUND_COLOR' => $shop->theme['primary_background_color'] ?? '#000000ff',
            'IN_THEME_PRIMARY_TEXT_COLOR' => $shop->theme['primary_text_color'] ?? '#ffffffff',
            'IN_THEME_SECONDARY_BACKGROUND_COLOR' => $shop->theme['secondary_background_color'] ?? '#ffffffff',
            'IN_THEME_SECONDARY_TEXT_COLOR' => $shop->theme['secondary_text_color'] ?? '#000000ff',
            'CAMPAIGN_NAME' => $this->name,
            'CAMPAIGN_UID' => $this->uid,
            'CAMPAIGN_SUBJECT' => $this->subject,
            'CAMPAIGN_FROM_EMAIL' => $this->from,
            'CAMPAIGN_FROM_NAME' => $this->from_name,
            'CAMPAIGN_REPLY_TO' => $this->reply_to,
            'SUBSCRIBER_UID' => $subscriber->uid,
            'CURRENT_YEAR' => date('Y'),
            'CURRENT_MONTH' => date('m'),
            'CURRENT_DAY' => date('d'),
            'CONTACT_NAME' => $subscriber->mailList->contact->company,
            'CONTACT_COUNTRY' => $subscriber->mailList->contact->countryName(),
            'CONTACT_STATE' => $subscriber->mailList->contact->state,
            'CONTACT_CITY' => $subscriber->mailList->contact->city,
            'CONTACT_ADDRESS_1' => $subscriber->mailList->contact->address_1,
            'CONTACT_ADDRESS_2' => $subscriber->mailList->contact->address_2,
            'CONTACT_PHONE' => $subscriber->mailList->contact->phone,
            'CONTACT_URL' => $subscriber->mailList->contact->url,
            'CONTACT_EMAIL' => $subscriber->mailList->contact->email,
            'LIST_NAME' => $subscriber->mailList->name,
            'LIST_SUBJECT' => $subscriber->mailList->default_subject,
            'LIST_FROM_NAME' => $subscriber->mailList->from_name,
            'LIST_FROM_EMAIL' => $subscriber->mailList->from_email,
        );

        /** @var ShopifyDiscountCode[] $discount_codes */
        $discount_codes = ShopifyDiscountCode::findByCustomer($this->automation->customer_id);
        foreach ($discount_codes as $code) {
            if ($code->shopify_price_rule && $code->shopify_price_rule->getShopifyModel())
                $tags['DISCOUNT.' . $code->discount_code . '.VALUE'] = $code->shopify_price_rule->getShopifyModel()->value;
        }

        # Subscriber specific
        if (!$this->isStdClassSubscriber($subscriber)) {
            $tags['UPDATE_PROFILE_URL'] = $this->generateUpdateProfileUrl($subscriber);
            $tags['UNSUBSCRIBE_URL'] = $this->generateUnsubscribeUrl($msgId, $subscriber);
            $tags['WEB_VIEW_URL'] = $this->generateWebViewerUrl($msgId);

            # Subscriber custom fields
            foreach ($subscriber->mailList->fields as $field) {
                $tags['SUBSCRIBER_' . $field->tag] = $subscriber->getValueByField($field);
            }
        } else {
            $tags['SUBSCRIBER_EMAIL'] = $subscriber->email;
        }

        /** @var ShopifyDiscountCode[] $discount_codes */
        $discount_codes = ShopifyDiscountCode::findByCustomer($this->automation->customer_id);
        foreach ($discount_codes as $code) {
            if ($code->shopify_price_rule && $code->shopify_price_rule->getShopifyModel())
                $tags['DISCOUNT.' . $code->discount_code . '.VALUE'] = $code->shopify_price_rule->getShopifyModel()->value;
        }

        switch ($this->automation->trigger_key) {
            case ShopifyAutomationContext::AUTOMATION_TRIGGER_REVIEW_SUBMITTED:
            case ShopifyAutomationContext::AUTOMATION_TRIGGER_REVIEW_UPDATED:
                if ($context && $context->shopifyReview) {
                    $tags['REVIEW_EDIT_LINK'] = $context->shopifyReview->getReviewGuestEditPageUrl();
                }
                if ($context && $context->shopifyReview && $context->shopifyReview->product) {
                    $tags['PRODUCT_LINK'] = $context->shopifyReview->product->getProductLink();
                    $tags['PRODUCT_TITLE'] = $context->shopifyReview->product->shopify_title;
                }
                break;
            case ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGER_EMAILWISH_ORDER_PLACED:
            case ShopifyAutomationContext::AUTOMATION_TRIGGER_SHOPIFY_ORDER_PLACED:
            case ShopifyAutomationContext::AUTOMATION_TRIGGER_SHOPIFY_ORDER_DELIVERED:
            case ShopifyAutomationContext::AUTOMATION_TRIGGER_SHOPIFY_ORDER_FULFILLED:
                if ($context && $context->shopifyOrder) {
                    $tags['PRODUCT_LINK'] = $context->shopifyOrder->getFirstProductLink();
                    $tags['PRODUCT_TITLE'] = $context->shopifyOrder->getFirstProductTitle();
                    $tags['DISCOUNT_CODE'] = $context->shopifyOrder->getFirstDiscountCode();
                    $tags['DISCOUNT_AMOUNT'] = $context->shopifyOrder->getFirstDiscountAmount();
                }
                break;
            case ShopifyAutomationContext::AUTOMATION_TRIGGER_SHOPIFY_CHECKOUT_ABANDONED:
                if ($context && $context->shopifyAbandonedCheckout) {
                    $tags['ABANDONED_CHECKOUT_LINK'] = $context->shopifyAbandonedCheckout->getShopifyModel()->abandoned_checkout_url;
                    $tags['PRODUCT_LINK'] = $context->shopifyAbandonedCheckout->getFirstProductLink();
                    $tags['PRODUCT_TITLE'] = $context->shopifyAbandonedCheckout->getFirstProductTitle();
                }
                break;
        }

        // Actually transform the message
        foreach ($tags as $tag => $value) {
            $message = str_ireplace('{' . $tag . '}', $value, $message);
        }

        return $message;
    }

    function replaceDynamicWidgets($body, ShopifyAutomationContext $context)
    {
        if ($context && $context->shopifyAbandonedCheckout) {
            $body = ShopifyCheckout::prepareAbandonedCart($context->shopifyAbandonedCheckout, $body);
        }
        if ($context && $context->shopifyFulfillment) {
            $body = ShopifyOrder::prepareReviewEmail($body, $context->shopifyFulfillment);
        }
        if ($context && $context->shopifyOrder) {
            $body = ShopifyOrder::prepareOrderEmail($context->shopifyOrder, $body);
        }
        $body = ShopifyProduct::prepareDynamicProducts($body);

        // find all iframe tags in html content
        preg_match_all('/(<iframe[^>]+src=)([\'"])(?<src>.*?)(\2)[^>]*>/i', $body, $matches);
        foreach ($matches as $match) {
            if (empty($match))
                continue;
            $tag = $match[0];
            $src = parse_url($match['src']);

            $new_tag = "";
            if ($src['path'] == "/ew_dynamic/iframe") {
                parse_str($src['query'], $query);
                switch ($query['type'] ?? '') {
                    case 'product':
                        if ($context && $context->shopifyReview && $context->shopifyReview->product)
                            $new_tag = ShopifyProduct::getHtmlRepresentation(new ProductConfig($context->shopifyReview->product, $src['query']));
                        break;
                    case 'products3':
                        if ($context && $context->shopifyReview && $context->shopifyReview->product)
                            $new_tag = ShopifyProduct::getProduct3HtmlRepresentation(new Products3Config($src['query']));
                        break;
                    case 'order':
                        if ($context && $context->shopifyOrder)
                            $new_tag = ShopifyOrder::getHtmlRepresentation($context->shopifyOrder, new OrderConfig($src['query']));
                        break;
                    case 'abandoned_cart':
                        if ($context && $context->shopifyAbandonedCheckout)
                            $new_tag = ShopifyCheckout::getHtmlRepresentation($context->shopifyAbandonedCheckout, new CartConfig($src['query']));
                        break;
                    case 'chat':
                        if ($context && $context->chatSession)
                            $new_tag = ChatSession::getHtmlRepresentation($context->chatSession, new ChatConfig($src['query']));
                        break;
                }
            }

            $body = str_replace($tag, $new_tag, $body);
        }

        return $body;
    }

    /**
     * Check if the given variable is a subscriber object (for actually sending a campaign)
     * Or a stdClass subscriber (for sending test email).
     *
     * @param object $object
     */
    public function isStdClassSubscriber($object)
    {
        return get_class($object) == 'stdClass';
    }

    public function generateUpdateProfileUrl($subscriber)
    {
        $path = route('updateProfileUrl', ['list_uid' => $subscriber->mailList->uid, 'uid' => $subscriber->uid, 'code' => $subscriber->getSecurityToken('update-profile')], false);

        return $this->buildPublicUrl($path);
    }

    public function generateWebViewerUrl($msgId)
    {
        $path = route('webViewerUrl', ['message_id' => StringHelper::base64UrlEncode($msgId)], false);

        return $this->buildPublicUrl($path);
    }

    function getContent(): string
    {
        if (str_starts_with($this->content, '<div class="container_bg"'))
            return $this->content;

        return '<div class="container_bg" style="background-color:#f5f5f5"><div class="container">' . $this->content . '</div></div>';
    }

    function getPlainContent($html)
    {
        $style_removed = preg_replace('/<style>.*?<\/style>/s', '', $html);
        $with_newlines = str_replace(["</div>", "</p>", "</h1>", "</h2>", "</h3>", "&nbsp;"], ["</div>\n", "</p>\n", "</h1>\n", "</h2>\n", "</h3>\n", "\n"], $style_removed);
        $single_newlines = str_replace("\n\n", "\n", $with_newlines);
        return preg_replace('/[\t\f ]+/', ' ', preg_replace('/[\r\n]+/', "\n", strip_tags($single_newlines)));
    }

    /**
     * Build Email HTML content.
     *
     * @return string
     */
    public function getHtmlContent($subscriber = null, $msgId = null, $server = null, ShopifyAutomationContext $context = null)
    {
        // @note: IMPORTANT: the order must be as follows
        // * addTrackingURL
        // * appendOpenTrackingUrl
        // * tagMessage
        if (is_null($this->parsedContent)) {
            // STEP 01. Get RAW content
            $body = $this->content;

            // STEP 02. Append footer
            if ($this->footerEnabled()) {
                $body = $this->appendFooter($body, $this->getHtmlFooter());
            }

            // STEP 03. Parse RSS
            if (Setting::isYes('rss.enabled')) {
                $body = Rss::parse($body);
            }

            // STEP 04. Replace Bare linefeed
            // Replace bare line feed char which is not accepted by Outlook, Yahoo, AOL...
            $body = StringHelper::replaceBareLineFeed($body);
            $body = ChatSession::getHtmlRepresentation($body, $this->automation->customer, $subscriber->id ?? '', null);
        
            // "Cache" it, do not repeat this task for every subscriber in the loop
            $this->parsedContent = $body;
        } else {
            // Retrieve content from cache
            $body = $this->parsedContent;
        }
        $body = StringHelper::removeDataImg($body);
        $body = ShopifyProduct::getBestSellingHtml($body, $this->automation->customer);
        // STEP 05. Transform URLs
        $body = $this->transformWithoutUpdate($body, true);

        if (is_null($msgId)) {
            $msgId = 'SAMPLE'; // for preview mode
        }

        // STEP 06. Add Click Tracking
        //
        // @note: addTrackingUrl() must go before appendOpenTrackingUrl()
        // Enable click tracking
        if ($this->track_click) {
            $body = $this->addTrackingUrl($body, $msgId);
        }

        // STEP 07. Add Open Tracking
        if ($this->track_open) {
            $body = $this->appendOpenTrackingUrl($body, $msgId);
        }

        // STEP 08. Transform Tags
        if (!is_null($subscriber)) {
            // Transform tags
            $body = $this->tagMessage($body, $subscriber, $msgId, $server, $context);
        }

        // Step 09. Replace dynamic widgets with actual content
        if (!is_null($context)) {
            $body = $this->replaceDynamicWidgets($body, $context);
        }

        // STEP 09. Make CSS inline
        //
        // Transform CSS/HTML content to inline CSS
        // Be carefule, it will make
        //       <a href="{UNSUBSCRIBE_URL}"
        // become
        //       <a href="%7BUNSUBSCRIBE_URL%7D" 
        $body = $this->inlineHtml($body);

        $files = [
            public_path('/cb/assets/minimalist-blocks/contentmedia.css'),
            public_path('/cb/assets/minimalist-blocks/contentmedia1.css'),
            public_path('/cb/assets/minimalist-blocks/contentmedia2.css')
        ];
        $css = "";
        foreach ($files as $file)
            $css .= '<style>' . file_get_contents($file) . '</style>';

        // Additional Step: Add HTML
        $body = '<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <title>' . $this->name . '</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    ' . $css . '
</head>
<body>
' . $body . '
</body>
</html>';

        return $body;
    }

    /**
     * Replace link in text by click tracking url.
     *
     * @return text
     * @note addTrackingUrl() must go before appendOpenTrackingUrl()
     */
    public function addTrackingUrl($email_html_content, $msgId)
    {
        if (preg_match_all('/<a[^>]*href=["\'](?<url>http[^"\']*)["\']/i', $email_html_content, $matches)) {
            foreach ($matches[0] as $key => $href) {
                $url = $matches['url'][$key];

                $newUrl = route('clickTrackingUrl', ['message_id' => StringHelper::base64UrlEncode($msgId), 'url' => StringHelper::base64UrlEncode($url)], false);
                $newUrl = $this->buildTrackingUrl($newUrl);
                $newHref = str_replace($url, $newUrl, $href);

                // if the link contains UNSUBSCRIBE URL tag
                if (strpos($href, '{UNSUBSCRIBE_URL}') !== false) {
                    // just do nothing
                } elseif (preg_match('/{[A-Z0-9_]+}/', $href)) {
                    // just skip if the url contains a tag. For example: {UPDATE_PROFILE_URL}
                    // @todo: do we track these clicks?
                } else {
                    $email_html_content = str_replace($href, $newHref, $email_html_content);
                }
            }
        }

        return $email_html_content;
    }

    /**
     * Append Open Tracking URL
     * Append open-tracking URL to every email message.
     */
    public function appendOpenTrackingUrl($body, $msgId)
    {
        $path = route('openTrackingUrl', ['message_id' => StringHelper::base64UrlEncode($msgId)], false);
        $url = $this->buildTrackingUrl($path);

        return $body . '<img src="' . $url . '" width="0" height="0" alt="" style="visibility:hidden" />';
    }

    public function buildTrackingUrl($path)
    {
        $host = $this->getTrackingHost();

        return join_url($host, $path);
    }

    public function getTrackingHost()
    {
        if ($this->trackingDomain()->exists()) {
            return $this->trackingDomain->getUrl();
        } else {
            return config('app.url');
        }
    }

    /**
     * Check if email footer enabled.
     *
     * @return string
     */
    public function footerEnabled()
    {
        return $this->automation->customer->getActiveShopifyRecurringApplicationCharge()->plan->getOption('email_footer_enabled') == 'yes';
    }

    /**
     * Get HTML footer.
     *
     * @return string
     */
    public function getHtmlFooter()
    {
        return $this->automation->customer->getActiveShopifyRecurringApplicationCharge()->plan->getOption('html_footer');
    }

    /**
     * Append footer.
     *
     * @return string.
     */
    public function appendFooter($body, $footer)
    {
        return $body . $footer;
        //return preg_replace('/<\/\s*html\s*>/i', $footer . '</html>', $body);
    }

    /**
     * Convert html to inline.
     *
     * @todo not very OOP here, consider moving this to a Helper instead
     */
    public function inlineHtml($html)
    {
        // Convert to inline css
        $cssToInlineStyles = new CssToInlineStyles();
        $css = "";
        $files = [
            public_path('/cb/assets/minimalist-blocks/content.css'),
            public_path('/cb/assets/custom.css'),
            public_path('/cb/contentbuilder/contentbuilder.css')
        ];
        foreach ($files as $file)
            $css .= file_get_contents($file);

        // output
        $html = $cssToInlineStyles->convert(
            $html,
            $css
        );

        return $html;
    }

    /**
     * Find sending domain from email.
     *
     * @param $email
     * @return SendingDomain|null
     */
    public function findSendingDomainWithActiveDkim($email)
    {
        $domainName = substr(strrchr($email, '@'), 1);

        if ($domainName == false) {
            return null;
        }

        $domain = $this->automation->customer->activeDkimSendingDomains()->where('name', $domainName)->first();
        if (is_null($domain)) {
            $domain = SendingDomain::getAllAdminActive()->where('name', $domainName)->first();
        }

        return $domain;
    }

    /**
     * Sign the message with DKIM.
     *
     * @param $message
     * @param SendingServer $server
     * @return mixed
     */
    public function sign($message, $server)
    {
        $sendingDomain = $this->findSendingDomainWithActiveDkim($this->from_email);

        if (empty($sendingDomain)) {
            // Domain doesn't have DKIM.
            // Attempt to sign by sending server
            if ($server)
                $message = $server->sign($message);
            return $message;
        }

        $privateKey = $sendingDomain->dkim_private;
        $domainName = $sendingDomain->name;
        $selector = $sendingDomain->getDkimSelectorParts()[0];
        $signer = new \Swift_Signers_DKIMSigner($privateKey, $domainName, $selector);
        $signer->ignoreHeader('Return-Path');
        $message->attachSigner($signer);

        return $message;
    }

    public function isOpened($subscriber): bool
    {
        return $this->trackingLogs()->where('subscriber_id', $subscriber->id)
            ->join('open_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')->exists();
    }

    public function isClicked($subscriber): bool
    {
        return $this->trackingLogs()->where('subscriber_id', $subscriber->id)
            ->join('click_logs', 'click_logs.message_id', '=', 'tracking_logs.message_id')->exists();
    }

    /**
     * Check if email has template.
     */
    public function hasTemplate()
    {
        return $this->content;
    }

    /**
     * Remove email template.
     */
    public function removeTemplate()
    {
        $this->content = null;
        Tool::xdelete($this->getStoragePath());
        $this->save();
    }

    /**
     * Update email plain text.
     */
    public function updatePlainFromContent()
    {
        if (!$this->plain) {
            $this->plain = preg_replace('/\s+/', ' ', preg_replace('/\r\n/', ' ', strip_tags($this->content)));
            $this->save();
        }
    }

    /**
     * Fill email's fields from request.
     */
    public function fillAttributes($params)
    {
        $this->fill($params);

        // Tacking domain
        if (isset($params['custom_tracking_domain']) && $params['custom_tracking_domain'] && isset($params['tracking_domain_uid'])) {
            $tracking_domain = TrackingDomain::findByUid($params['tracking_domain_uid']);
            if (is_object($tracking_domain)) {
                $this->tracking_domain_id = $tracking_domain->id;
            } else {
                $this->tracking_domain_id = null;
            }
        } else {
            $this->tracking_domain_id = null;
        }
    }

    function replaceInternalTags()
    {
        $sig = $this->automation->customer->signature;
        $shop = $this->automation->customer->shopify_shop;

        $tags = [
            'IN_WEBSITE' => $sig ? $sig->website : "",
            'IN_ADDRESS' => $this->automation->customer->getContact()->getAddress(),
            'IN_BRANDNAME' => $this->automation->customer->shopify_shop->name,
            'IN_EMAIL' => $this->automation->customer->user->email,
            'IN_FIRSTNAME' => $this->automation->customer->user->first_name,
            'IN_FULLNAME' => $sig ? $sig->full_name : "",
            'IN_POSITION' => $sig ? $sig->designation : "",
            'IN_PHONE' => $sig ? $sig->phone : "",
            'IN_FACEBOOK' => $sig ? $sig->facebook : "",
            'IN_INSTAGRAM' => $sig ? $sig->instagram : "",
            'IN_LINKEDIN' => $sig ? $sig->linkedin : "",
            'IN_TWITTER' => $sig ? $sig->twitter : "",
            'IN_SKYPE' => $sig ? $sig->skype : "",
            'IN_YOUTUBE' => $sig ? $sig->youtube : "",
            'IN_TIKTOK' => $sig ? $sig->tiktok : "",
            'IN_PINTEREST' => $sig ? $sig->pinterest : "",
            'IN_LOGO_URL' => $sig && $sig->logo_url ? $sig->logo_url : "/cb/assets/minimalist-blocks/images/logoplaceholder.png",
            'IN_THEME_FONT_FAMILY' => $shop->theme['font_family'] ?? 'Roboto',
            'IN_THEME_PRIMARY_BACKGROUND_COLOR' => $shop->theme['primary_background_color'] ?? '#000000ff',
            'IN_THEME_PRIMARY_TEXT_COLOR' => $shop->theme['primary_text_color'] ?? '#ffffffff',
            'IN_THEME_SECONDARY_BACKGROUND_COLOR' => $shop->theme['secondary_background_color'] ?? '#ffffffff',
            'IN_THEME_SECONDARY_TEXT_COLOR' => $shop->theme['secondary_text_color'] ?? '#000000ff',
        ];

        foreach ($tags as $tag => $value) {
            $this->content = str_ireplace('{' . $tag . '}', $value, $this->content);
        }
    }

    public function makeCopy(Automation2 $newAutomation): self
    {
        $new = new self();
        $new->automation2_id = $newAutomation->id;
        $new->subject = $this->subject;
        $new->content = $this->content;
        $new->sign_dkim = $this->sign_dkim;
        $new->track_open = $this->track_open;
        $new->track_click = $this->track_click;
        $new->action_id = $this->action_id;
        $new->plain = $this->plain;
        if ($newAutomation->customer_id == $this->automation->customer_id) {
            $new->from = $this->from;
            $new->from_name = $this->from_name;
            $new->reply_to = $this->reply_to;
            $new->tracking_domain_id = $this->tracking_domain_id;
        } else {
            $new->from = $newAutomation->customer->email();
            $new->from_name = $newAutomation->customer->displayName();
            $new->reply_to = $newAutomation->customer->email();
            $new->tracking_domain_id = null;
        }
        $new->replaceInternalTags();
        $new->save();

        foreach ($this->attachments as $attachment) {
            $attachment->makeCopy($new);
        }

        foreach ($this->emailLinks as $emailLink) {
            $emailLink->makeCopy($new);
        }
        return $new;
    }
}
