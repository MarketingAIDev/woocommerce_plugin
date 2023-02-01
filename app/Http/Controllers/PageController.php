<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\Layout;
use Acelle\Model\Page;
use Acelle\Model\Subscriber;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Acelle\Model\Setting;
use Acelle\Model\MailList;
use Illuminate\Support\Facades\Gate;

class PageController extends Controller
{
    public function __construct()
    {
        $this->middleware('selected_customer')->except(['unsubscribeForm', 'unsubscribeSuccessPage']);
        parent::__construct();
    }
    /**
     * Redirect page if use outside url.
     */
    public function checkOutsideUrlRedirect($page)
    {
        if ($page->use_outside_url) {
            return redirect($page->outside_url);
        }
    }

    /**
     * Update list page content.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function update(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);

        // authorize
        if (Gate::denies('update', $list)) {
            if($request->wantsJson()){
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        $layout = Layout::where('alias', $request->alias)->first();
        $page = Page::findPage($list, $layout);

        // storing
        if ($request->isMethod('post')) {
            $page->fill($request->all());

            $validate = 'required';
            foreach ($layout->tags() as $tag) {
                if ($tag['required']) {
                    $validate .= '|substring:'.$tag['name'];
                }
            }

            $rules = array();

            // Check if use outside url
            if ($request->use_outside_url) {
                $rules['outside_url'] = 'active_url';
            } else {
                $rules['content'] = $validate;
                $rules['subject'] = 'required';
            }

            // Validation
            $this->validate($request, $rules);

            // save
            $page->save();

            // Log
            $page->log('updated', $request->selected_customer);

            $request->session()->flash('alert-success', trans('messages.page.updated'));

            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => 'PageController@update',
                    'list_uid'=>$list->uid,
                    'alias'=>$layout->alias
                ]);
            }
            return redirect()->action('PageController@update', array('list_uid' => $list->uid, 'alias' => $layout->alias));
        }

        // return back
        $page->fill($request->old());

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'pages.update',
                'list' => $list,
                'page' => $page,
                'layout' => $layout,
            ]);
        }
        return view('pages.update', [
            'list' => $list,
            'page' => $page,
            'layout' => $layout,
        ]);
    }

    /**
     * Preview page.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function preview(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);

        // authorize
        if (Gate::denies('update', $list)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'notAuthorized'
                ]);
            }
            return $this->notAuthorized();
        }

        $layout = Layout::where('alias', $request->alias)->first();
        $page = Page::findPage($list, $layout);
        $page->content = $request->content;

        // render content
        $page->renderContent();

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'pages.preview_'.$page->layout->type,
                'list' => $list,
                'page' => $page,
            ]);
        }
        return view('pages.preview_'.$page->layout->type, [
            'list' => $list,
            'page' => $page,
        ]);
    }

    /**
     * Sign up form page.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function signUpForm(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);
        $layout = Layout::where('alias', 'sign_up_form')->first();
        $page = Page::findPage($list, $layout);

        // Get old post values
        $values = [];
        if (null !== $request->old()) {
            foreach ($request->old() as $key => $value) {
                if (is_array($value)) {
                    $values[str_replace('[]', '', $key)] = implode(',', $value);
                } else {
                    $values[$key] = $value;
                }
            }
        }

        $page->renderContent($values);

        // Create subscriber
        if ($request->isMethod('post')) {
            try {
                list($validator, $subscriber) = $list->subscribe($request, MailList::SOURCE_WEB);
            } catch (Exception $ex) {
                return view('somethingWentWrong', ['message' => $ex->getMessage()]);
            }

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            if ($request->redirect_url) {
                return redirect()->away($request->redirect_url);
            } else if ($list->subscribe_confirmation && !$subscriber->isSubscribed()) {
                // tell subscriber to check email for confirmation
                return redirect()->action('PageController@signUpThankyouPage', ['list_uid' => $list->uid, 'subscriber_uid' => $subscriber->uid]);
            } else {
                // All done, confirmed
                return redirect()->action(
                    'PageController@signUpConfirmationThankyou', [
                        'list_uid' => $list->uid,
                        'uid' => $subscriber->uid,
                        'code' => 'empty',
                    ]
                );
            }
        }

        return view('pages.form', [
            'list' => $list,
            'page' => $page,
            'values' => $values,
        ]);
    }

    /**
     * Sign up thank you page.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function signUpThankyouPage(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);
        $layout = Layout::where('alias', 'sign_up_thankyou_page')->first();
        $page = Page::findPage($list, $layout);
        $subscriber = Subscriber::findByUid($request->subscriber_uid);

        // redirect if use outside url
        if ($page->use_outside_url) {
            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => $page->getOutsideUrlWithUid($subscriber)
                ]);
            }
            return redirect($page->getOutsideUrlWithUid($subscriber));
        }

        $page->renderContent(null, $subscriber);

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'pages.default',
                'list' => $list,
                'page' => $page,
            ]);
        }
        return view('pages.default', [
            'list' => $list,
            'page' => $page,
        ]);
    }

    /**
     * Sign up confirmation thank you page.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function signUpConfirmationThankyou(Request $request)
    {
        $user = $request->user();
        $list = MailList::findByUid($request->list_uid);
        $layout = Layout::where('alias', 'sign_up_confirmation_thankyou')->first();
        $page = Page::findPage($list, $layout);
        $subscriber = Subscriber::findByUid($request->uid);

        if (is_null($subscriber)) {
            echo "Subscriber no longer exists";
            return;
        }

        $page->renderContent(null, $subscriber);

        if ($subscriber->getSecurityToken('subscribe-confirm') == $request->code && $subscriber->status == 'unconfirmed') {
            $subscriber->status = 'subscribed';
            $subscriber->save();

            // Send welcome email
            if ($list->send_welcome_email) {
                // SEND subscription confirmation email
                try {
                    $list->sendSubscriptionWelcomeEmail($subscriber);
                } catch (Exception $ex) {
                    if ($request->wantsJson()) {
                        return response()->json([
                            'view' => 'somethingWentWrong',
                            'message' => $ex->getMessage()
                        ]);
                    }
                    return view('somethingWentWrong', ['message' => $ex->getMessage()]);
                }
            }
        }

        // redirect if use outside url
        if ($page->use_outside_url) {
            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => $page->getOutsideUrlWithUid($subscriber)
                ]);
            }
            return redirect($page->getOutsideUrlWithUid($subscriber));
        }

        if($request->wantsJson()){
            return response()->json([
                'view' => 'pages.default',
                'list' => $list,
                'page' => $page,
            ]);
        }
        return view('pages.default', [
            'list' => $list,
            'page' => $page,
        ]);
    }

    /**
     * Unsibscribe form.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function unsubscribeForm(Request $request)
    {
        // IMPORTANT: it does not create TrackingLog!!!
        $user = $request->user();
        $list = MailList::findByUid($request->list_uid);
        $layout = Layout::where('alias', 'unsubscribe_form')->first();
        $page = Page::findPage($list, $layout);
        $subscriber = Subscriber::findByUid($request->uid);

        $page->renderContent(null, $subscriber);

        if ($request->isMethod('post')) {
            if ($subscriber->getSecurityToken('unsubscribe') == $request->code && $subscriber->status == 'subscribed') {
                $subscriber->status = 'unsubscribed';
                $subscriber->save();

                // Send goodbye email
                if ($list->unsubscribe_notification) {
                    // SEND subscription confirmation email
                    try {
                        $list->sendUnsubscriptionNotificationEmail($subscriber);
                    } catch (Exception $ex) {
                        if($request->wantsJson()){
                            return response()->json([
                                'view' => 'somethingWentWrong',
                                'message' => $ex->getMessage()
                            ]);
                        }
                        return view('somethingWentWrong', ['message' => $ex->getMessage()]);
                    }
                }

                if (Setting::isYes('send_notification_email_for_list_subscription')) {
                    $list->sendUnsubscriptionNotificationEmailToListOwner($subscriber);
                }
            }

            return redirect()->action('PageController@unsubscribeSuccessPage', ['list_uid' => $list->uid, 'uid' => $subscriber->uid]);
        }

        return view('pages.form', [
            'list' => $list,
            'page' => $page,
        ]);
    }

    /**
     * Unsibscribe form.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function unsubscribeSuccessPage(Request $request)
    {
        $user = $request->user();
        $list = MailList::findByUid($request->list_uid);
        $layout = Layout::where('alias', 'unsubscribe_success_page')->first();
        $page = Page::findPage($list, $layout);
        $subscriber = Subscriber::findByUid($request->uid);

        $page->renderContent(null, $subscriber);

        // redirect if use outside url
        if ($page->use_outside_url) {
            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => $page->getOutsideUrlWithUid($subscriber)
                ]);
            }
            return redirect($page->getOutsideUrlWithUid($subscriber));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'pages.default',
                'list' => $list,
                'page' => $page,
                'subscriber' => $subscriber,
            ]);
        }
        return view('pages.default', [
            'list' => $list,
            'page' => $page,
            'subscriber' => $subscriber,
        ]);
    }

    /**
     * Update profile form.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function profileUpdateForm(Request $request)
    {
        $user = $request->user();
        $list = MailList::findByUid($request->list_uid);
        $layout = Layout::where('alias', 'profile_update_form')->first();
        $page = Page::findPage($list, $layout);
        $subscriber = Subscriber::findByUid($request->uid);

        $values = [];

        // Fetch subscriber fields to values
        foreach ($list->fields as $key => $field) {
            $value = $subscriber->getValueByField($field);
            if (is_array($value)) {
                $values[str_replace('[]', '', $key)] = implode(',', $value);
            } else {
                $values[$field->tag] = $value;
            }
        }

        // Get old post values
        if (null !== $request->old()) {
            foreach ($request->old() as $key => $value) {
                if (is_array($value)) {
                    $values[str_replace('[]', '', $key)] = implode(',', $value);
                } else {
                    $values[$key] = $value;
                }
            }
        }

        $page->renderContent($values, $subscriber);

        if ($request->isMethod('post')) {
            if ($subscriber->getSecurityToken('update-profile') == $request->code) {
                $rules = $subscriber->getRules();
                $rules['EMAIL'] .= '|in:'.$subscriber->email;
                // Validation
                $this->validate($request, $rules);

                // Update field
                $subscriber->updateFields($request->all());

                if ($request->wantsJson()) {
                    return response()->json([
                        'redirectAction' => 'PageController@profileUpdateSuccessPage',
                        'list_uid' => $list->uid,
                        'uid' => $subscriber->uid
                    ]);
                }
                return redirect()->action('PageController@profileUpdateSuccessPage', [
                    'list_uid' => $list->uid,
                    'uid' => $subscriber->uid
                ]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'pages.form',
                'list' => $list,
                'page' => $page,
                'subscriber' => $subscriber,
            ]);
        }
        return view('pages.form', [
            'list' => $list,
            'page' => $page,
            'subscriber' => $subscriber,
        ]);
    }

    /**
     * Update profile success.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function profileUpdateSuccessPage(Request $request)
    {
        $user = $request->user();
        $list = MailList::findByUid($request->list_uid);
        $layout = Layout::where('alias', 'profile_update_success_page')->first();
        $page = Page::findPage($list, $layout);
        $subscriber = Subscriber::findByUid($request->uid);

        $page->renderContent(null, $subscriber);

        // redirect if use outside url
        if ($page->use_outside_url) {
            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => $page->getOutsideUrlWithUid($subscriber)
                ]);
            }
            return redirect($page->getOutsideUrlWithUid($subscriber));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'pages.default',
                'list' => $list,
                'page' => $page,
                'subscriber' => $subscriber,
            ]);
        }
        return view('pages.default', [
            'list' => $list,
            'page' => $page,
            'subscriber' => $subscriber,
        ]);
    }

    /**
     * Send update profile request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function profileUpdateEmailSent(Request $request)
    {
        $user = $request->user();
        $list = MailList::findByUid($request->list_uid);
        $layout = Layout::where('alias', 'profile_update_email_sent')->first();
        $page = Page::findPage($list, $layout);
        $subscriber = Subscriber::findByUid($request->uid);

        $page->renderContent(null, $subscriber);

        // SEND EMAIL
        try {
            $list->sendProfileUpdateEmail($subscriber);
        } catch (Exception $ex) {
            if ($request->wantsJson()) {
                return response()->json([
                    'view' => 'somethingWentWrong',
                    'message' => $ex->getMessage()
                ]);
            }
            return view('somethingWentWrong', ['message' => $ex->getMessage()]);
        }

        // redirect if use outside url
        if ($page->use_outside_url) {
            if ($request->wantsJson()) {
                return response()->json([
                    'redirectAction' => $page->getOutsideUrlWithUid($subscriber)
                ]);
            }
            return redirect($page->getOutsideUrlWithUid($subscriber));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'pages.default',
                'list' => $list,
                'page' => $page,
                'subscriber' => $subscriber,
            ]);
        }
        return view('pages.default', [
            'list' => $list,
            'page' => $page,
            'subscriber' => $subscriber,
        ]);
    }
}
