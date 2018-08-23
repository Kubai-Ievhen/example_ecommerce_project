<?php

namespace App\Http\Controllers;

use App\ActivitiesUsersType;
use App\BaseMessageTemplate;
use App\BaseSEOData;
use App\IconsGroup;
use App\InputMessage;
use App\MailTemplate;
use App\MailToGroup;
use App\MailToUser;
use App\MessageSpecialeDate;
use App\SingleMailToGroup;
use App\SingleMailToUser;
use App\SingleMessage;
use App\TimeDispatchMessages;
use App\User;
use App\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**@api AdminMessageController
 * @apiName AdminMessageController
 * @apiGroup AdminMessageController
 * @apiDescription App\Http\Controllers Class AdminMessageController
 */
class AdminMessageController extends AdminBaseController
{
    /** @api {public} getMessagesInbox() getMessagesInbox()
     * @apiName getMessagesInbox()
     * @apiGroup AdminMessageController
     * @apiDescription  Get page with inbox messages.
     */
    public function getMessagesInbox() {
        $data = $this->getMessage('recipient_user_id');

        return view('admin.pages.messages.inbox', $data);
    }

    /** @api {public} getMessagesOutbox() getMessagesOutbox()
     * @apiName getMessagesOutbox()
     * @apiGroup AdminMessageController
     * @apiDescription  Get page with outbox messages.
     */
    public function getMessagesOutbox() {
        $data = $this->getMessage('sender_user_id');

        return view('admin.pages.messages.outbox', $data);
    }

    /** @api {private} getMessage() getMessage()
     * @apiName getMessage()
     * @apiGroup AdminMessageController
     * @apiDescription  Get inbox or outbox message for admin.
     * @apiParam {string} type Type for get data. 'recipient_user_id' for inbox message. sender_user_id for outbox message.

     */
    private function getMessage($type){
        $this->data['icons'] = IconsGroup::where('id','>',1)->with('images')->get();
        $this->data['base_seo'] = BaseSEOData::all();
        $groups = UserGroup::where('base_group_id', '>', '3')->pluck('id')->toArray();

        $this->data['users'] = User::whereIn('group_id', $groups)->get();
        $this->data['messages'] = InputMessage::where('id', '>',0)->where($type, 0)->with('sender', 'recipient')->get();

        return $this->data;
    }

    /** @api {public} deleteMessage() deleteMessage()
     * @apiName deleteMessage()
     * @apiGroup AdminMessageController
     * @apiDescription  Delete message
     * @apiParam {int} id Id of message
     * @apiSuccess {bool} status Operations status
     * @apiSuccessExample Success-Response:
     *  ['status' => 1]
     */
    public function deleteMessage($id){
        InputMessage::where('id',$id)->delete();

        return ['status'=>1];
    }

    /** @api {public} deleteCheckedMessage() deleteCheckedMessage()
     * @apiName deleteCheckedMessage()
     * @apiGroup AdminMessageController
     * @apiDescription  Delete checked messages
     * @apiParam {array} checked Array of messages Id
     * @apiSuccess {bool} status Operations status
     * @apiSuccessExample Success-Response:
     *  ['status' => 1]
     */
    public function deleteCheckedMessage(Request $request){
        InputMessage::whereIn('id',$request['checked'])->delete();

        return ['status'=>1];
    }

    /** @api {public} deleteAllInputMessage() deleteAllInputMessage()
     * @apiName deleteAllInputMessage()
     * @apiGroup AdminMessageController
     * @apiDescription  Delete all inbox message
     * @apiSuccess {bool} status Operations status
     * @apiSuccessExample Success-Response:
     *  ['status' => 1]
     */
    public function deleteAllInputMessage(){
        InputMessage::where('recipient_user_id',0)->delete();

        return ['status'=>1];
    }

    /** @api {public} asReadMessage() asReadMessage()
     * @apiName asReadMessage()
     * @apiGroup AdminMessageController
     * @apiDescription  Mark as read message
     * @apiParam {int} id Id of message
     * @apiSuccess {bool} status Operations status
     * @apiSuccessExample Success-Response:
     *  ['status' => 1]
     */
    public function asReadMessage($id){
        InputMessage::where('id',$id)->update(['is_read'=>1]);

        return ['status'=>1];
    }

    /** @api {public} asReadCheckedMessage() asReadCheckedMessage()
     * @apiName asReadCheckedMessage()
     * @apiGroup AdminMessageController
     * @apiDescription  Mark as read checked messages
     * @apiParam {array} checked Array of messages Id
     * @apiSuccess {object} object Object with update data of message
     */
    public function asReadCheckedMessage(Request $request){
        InputMessage::whereIn('id',$request['checked'])->update(['is_read'=>1]);

        return InputMessage::whereIn('id',$request['checked'])->get();
    }

    /** @api {public} asReadAllInputMessage() asReadAllInputMessage()
     * @apiName asReadAllInputMessage()
     * @apiGroup AdminMessageController
     * @apiDescription  Mark as read all inbox message
     * @apiSuccess {object} object Object with update data of message
     */
    public function asReadAllInputMessage(){
        InputMessage::where('recipient_user_id',0)->update(['is_read'=>1]);

        return InputMessage::where('recipient_user_id',0)->get();
    }

    /** @api {public} asUnreadMessage() asUnreadMessage()
     * @apiName asUnreadMessage()
     * @apiGroup AdminMessageController
     * @apiDescription  Mark as unread message
     * @apiParam {int} id Id of message
     * @apiSuccess {bool} status Operations status
     * @apiSuccessExample Success-Response:
     *  ['status' => 1]
     */
    public function asUnreadMessage($id){
        InputMessage::where('id',$id)->update(['is_read'=>0]);

        return ['status'=>1];
    }

    /** @api {public} asUnreadCheckedMessage() asUnreadCheckedMessage()
     * @apiName asUnreadCheckedMessage()
     * @apiGroup AdminMessageController
     * @apiDescription  Mark as unread checked messages
     * @apiParam {array} checked Array of messages Id
     * @apiSuccess {object} object Object with update data of message
     */
    public function asUnreadCheckedMessage(Request $request){
        InputMessage::whereIn('id',$request['checked'])->update(['is_read'=>0]);

        return InputMessage::whereIn('id',$request['checked'])->get();
    }

    /** @api {public} asUnreadAllInputMessage() asUnreadAllInputMessage()
     * @apiName asUnreadAllInputMessage()
     * @apiGroup AdminMessageController
     * @apiDescription  Mark as unread all inbox message
     * @apiSuccess {object} object Object with update data of message
     */
    public function asUnreadAllInputMessage(){
        InputMessage::where('recipient_user_id',0)->update(['is_read'=>0]);

        return InputMessage::where('recipient_user_id',0)->get();
    }

    /** @api {public} asImportanceMessage() asImportanceMessage()
     * @apiName asImportanceMessage()
     * @apiGroup AdminMessageController
     * @apiDescription  Mark as Importance message
     * @apiParam {int} id Id of message
     * @apiSuccess {bool} status Operations status
     * @apiSuccessExample Success-Response:
     *  ['status' => 1]
     */
    public function asImportanceMessage($id){
        InputMessage::where('id',$id)->update(['is_important'=>1]);

        return ['status'=>1];
    }

    /** @api {public} asImportanceCheckedMessage() asImportanceCheckedMessage()
     * @apiName asImportanceCheckedMessage()
     * @apiGroup AdminMessageController
     * @apiDescription  Mark as Importance checked messages
     * @apiParam {array} checked Array of messages Id
     * @apiSuccess {object} object Object with update data of message
     */
    public function asImportanceCheckedMessage(Request $request){
        InputMessage::whereIn('id',$request['checked'])->update(['is_important'=>1]);

        return InputMessage::whereIn('id',$request['checked'])->get();
    }

    /** @api {public} asImportanceAllInputMessage() asImportanceAllInputMessage()
     * @apiName asImportanceAllInputMessage()
     * @apiGroup AdminMessageController
     * @apiDescription  Mark as Importance all inbox message
     * @apiSuccess {object} object Object with update data of message
     */
    public function asImportanceAllInputMessage(){
        InputMessage::where('recipient_user_id',0)->update(['is_important'=>1]);

        return InputMessage::where('recipient_user_id',0)->get();
    }

    /** @api {public} asUnimportanceMessage() asUnimportanceMessage()
     * @apiName asUnimportanceMessage()
     * @apiGroup AdminMessageController
     * @apiDescription  Mark as not important message
     * @apiParam {int} id Id of message
     * @apiSuccess {bool} status Operations status
     * @apiSuccessExample Success-Response:
     *  ['status' => 1]
     */
    public function asUnimportanceMessage($id){
        InputMessage::where('id',$id)->update(['is_important'=>0]);

        return ['status'=>1];
    }

    /** @api {public} asUnimportanceCheckedMessage() asUnimportanceCheckedMessage()
     * @apiName asUnimportanceCheckedMessage()
     * @apiGroup AdminMessageController
     * @apiDescription  Mark as not important checked messages
     * @apiParam {array} checked Array of messages Id
     * @apiSuccess {object} object Object with update data of message
     */
    public function asUnimportanceCheckedMessage(Request $request){
        InputMessage::whereIn('id',$request['checked'])->update(['is_important'=>0]);

        return InputMessage::whereIn('id',$request['checked'])->get();
    }

    /** @api {public} asUnimportanceAllInputMessage() asUnimportanceAllInputMessage()
     * @apiName asUnimportanceAllInputMessage()
     * @apiGroup AdminMessageController
     * @apiDescription  Mark as not important all inbox message
     * @apiSuccess {object} object Object with update data of message
     */
    public function asUnimportanceAllInputMessage(){
        InputMessage::where('recipient_user_id',0)->update(['is_important'=>0]);

        return InputMessage::where('recipient_user_id',0)->get();
    }

    /** @api {public} readMessage() readMessage()
     * @apiName readMessage()
     * @apiGroup AdminMessageController
     * @apiDescription  Return messages data
     * @apiParam {int} id Id of message
     * @apiSuccess {object} object Object with messages data
     */
    public function readMessage($id){
        InputMessage::where('id',$id)->where('sender_user_id', '>',0)->update(['is_read'=>1]);

        return InputMessage::where('id',$id)->with('sender', 'recipient')->first();
    }

    /** @api {public} replayMessage() replayMessage()
     * @apiName replayMessage()
     * @apiGroup AdminMessageController
     * @apiDescription  Replay message to message
     * @apiParam {int} id Id of message
     * @apiSuccess {bool} status Operations status
     * @apiSuccess {string} replay content message
     * @apiSuccessExample Success-Response:
     *  ['status' => 1]
     */
    public function replayMessage(Request $request, $id){
        $reply_massage = new InputMessage();
        $massage = InputMessage::find($id);

        $reply_massage->sender_user_id = 0;
        $reply_massage->recipient_user_id = $massage['sender_user_id'];
        $reply_massage->title = $massage['title'];
        $reply_massage->content = $request['replay'];

        $reply_massage->save();

        return ['status'=>1];
    }

    /** @api {public} getRegDispatchView() getRegDispatchView()
     * @apiName getRegDispatchView()
     * @apiGroup AdminMessageController
     * @apiDescription  Get page with regular dispatch data.
     */
    public function getRegDispatchView() {
        $this->data['base_templates'] = BaseMessageTemplate::all();
        $this->data['user_groups'] = UserGroup::all();
        $this->data['users'] = User::all();
        $this->data['activities'] = ActivitiesUsersType::all();
        $this->data['variables'] = MessageSpecialeDate::where('id','<=',5)->get();
        $this->data['mails'] = MailTemplate::all();

        return view('admin.pages.messages.regular_dispatch', $this->data);
    }

    /** @api {public} saveTimeManagement() saveTimeManagement()
     * @apiName saveTimeManagement()
     * @apiGroup AdminMessageController
     * @apiDescription  Update data of send regular messages
     * @apiParam {int} daily_hour Number of hour 0-23
     * @apiParam {int} weekly_hour Number of hour 0-23
     * @apiParam {int} weekly_day Number of day 0-6
     * @apiParam {int} monthly_hour Number of hour 0-23
     * @apiParam {int} monthly_day Number of day 0-30
     * @apiParam {int} annually_hour Number of hour 0-23
     * @apiParam {int} annually_day Number of day 0-30
     * @apiParam {int} annually_months Number of month 0-12
     * @apiSuccess {bool} status Operations status
     * @apiSuccessExample Success-Response:
     *  ['status' => 1]
     */
    public function saveTimeManagement(Request $request){
        TimeDispatchMessages::where('id',1)->update(['daily_hour' => $request['daily_hour'], 'weekly_hour' => $request['weekly_hour'],
            'weekly_day' => $request['weekly_day'], 'monthly_hour' => $request['monthly_hour'], 'monthly_day' => $request['monthly_day'],
            'annually_hour' => $request['annually_hour'], 'annually_day' => $request['annually_day'], 'annually_months' => $request['annually_months']]);

        return ['status' => 1];
    }

    /** @api {public} getTemplateMessage() getTemplateMessage()
     * @apiName saveTimeManagement()
     * @apiGroup AdminMessageController
     * @apiDescription  Get template of message
     * @apiParam {int} id Id of template message
     * @apiSuccess {object} content Templates data
     * @apiSuccess {object} variables Special variable for this template
     * @apiSuccessExample Success-Response:
     *  ['content' => object,
     *   'variables'=>object,
     *  ]
     */
    public function getTemplateMessage($id){
        $content    = BaseMessageTemplate::find($id);
        $variables_id = $content->variables()->pluck('message_speciale_date_id');
        $variables  =  MessageSpecialeDate::whereIn('id',$variables_id)->get();

        return ['content'=>$content, 'variables'=>$variables];
    }

    /** @api {private} saveMailTemplateDB() saveMailTemplateDB()
     * @apiName saveMailTemplateDB()
     * @apiGroup AdminMessageController
     * @apiDescription  Save mail template to database
     * @apiParam {object} request Data of request
     * @apiParam {object} mailTemplate Object of MailTemplate
     * @apiSuccess {object} object Object of MailTemplate
     */
    private function saveMailTemplateDB(Request $request, MailTemplate $mailTemplate){
        $mailTemplate->title           = $request['title'];
        $mailTemplate->content         = $request['content'];
        $mailTemplate->regularity      = $request['radio_inp'];
        $mailTemplate->note_type       = $request['note_type'];
        $mailTemplate->actions         = $request['actions_message'];
        $mailTemplate->base_template   = $request['base_template']?$request['base_template']:0;

        $mailTemplate->save();

        return $mailTemplate;
    }

    /** @api {private} saveSingleMailDB() saveSingleMailDB()
     * @apiName saveSingleMailDB()
     * @apiGroup AdminMessageController
     * @apiDescription  Save single mail to database
     * @apiParam {object} request Data of request
     * @apiParam {object} mailTemplate Object of SingleMessage
     * @apiSuccess {object} object Object of SingleMessage
     */
    private function saveSingleMailDB(Request $request, SingleMessage $mailTemplate){
        $dispatch_time = $this->getDataString($request);

        $mailTemplate->title           = $request['title'];
        $mailTemplate->content         = $request['content'];
        $mailTemplate->dispatch_time   = $dispatch_time;
        $mailTemplate->note_type       = $request['note_type'];
        $mailTemplate->timing_sent     = isset($request['date'])&&isset($request['time']);
        $mailTemplate->base_template   = $request['base_template']?$request['base_template']:0;

        $mailTemplate->save();

        return $mailTemplate;
    }

    /** @api {private} getDataString() getDataString()
     * @apiName getDataString()
     * @apiGroup AdminMessageController
     * @apiDescription  Date and time conversion
     * @apiParam {object} request Data of request
     * @apiSuccess {string} dispatch_time String with time for DB
     */
    private function getDataString(Request $request){
        if(strlen($request['date'])&&strlen($request['time'])){
            $dispatch_time = date_create_from_format('m/d/YG:i', $request['date'].$request['time']);
            $dispatch_time = $dispatch_time->format('Y-m-d G:i:s');
        }else{
            $dispatch_time = date('Y-m-d G:i:s');
        }

        return $dispatch_time;
    }

    /** @api {private} saveMailToUserDB() saveMailToUserDB()
     * @apiName saveMailToUserDB()
     * @apiGroup AdminMessageController
     * @apiDescription  Save mail to user
     * @apiParam {object} request Data of request
     * @apiParam {object} mailTemplate Object of MailTemplate
     */
    private function saveMailToUserDB(Request $request, MailTemplate $mailTemplate){
        if(count($request['user_select'])){
            foreach ($request['user_select'] as $user_select){
                $user = new MailToUser();

                $user->mail_template_id = $mailTemplate->id;
                $user->user_id = $user_select;

                $user->save();
            }
        }
    }

    /** @api {private} saveMailToGroupDB() saveMailToGroupDB()
     * @apiName saveMailToGroupDB()
     * @apiGroup AdminMessageController
     * @apiDescription  Save mail to users group
     * @apiParam {object} request Data of request
     * @apiParam {object} mailTemplate Object of MailTemplate
     */
    private function saveMailToGroupDB(Request $request, MailTemplate $mailTemplate){
        if(count($request['group_select'])){
            foreach ($request['group_select'] as $group_select){
                $group = new MailToGroup();

                $group->mail_template_id = $mailTemplate->id;
                $group->user_group_id = $group_select;

                $group->save();
            }
        }
    }

    /** @api {private} saveMailSingleToUserDB() saveMailSingleToUserDB()
     * @apiName saveMailSingleToUserDB()
     * @apiGroup AdminMessageController
     * @apiDescription  Save single mail to user
     * @apiParam {object} request Data of request
     * @apiParam {object} mailTemplate Object of MailTemplate
     */
    private function saveMailSingleToUserDB(Request $request, SingleMessage $mailTemplate){
        if(count($request['user_select'])){
            foreach ($request['user_select'] as $user_select){
                $user = new SingleMailToUser();

                $user->single_message_id = $mailTemplate->id;
                $user->user_id = $user_select;

                $user->save();
            }
        }
    }

    /** @api {private} saveMailSingleToGroupDB() saveMailSingleToGroupDB()
     * @apiName saveMailSingleToGroupDB()
     * @apiGroup AdminMessageController
     * @apiDescription  Save single mail to users group
     * @apiParam {object} request Data of request
     * @apiParam {object} mailTemplate Object of MailTemplate
     */
    private function saveMailSingleToGroupDB(Request $request, SingleMessage $mailTemplate){
        if(count($request['group_select'])){
            foreach ($request['group_select'] as $group_select){
                $group = new SingleMailToGroup();

                $group->single_message_id = $mailTemplate->id;
                $group->user_group_id = $group_select;

                $group->save();
            }
        }
    }

    /** @api {private} saveNewMessage() saveNewMessage()
     * @apiName saveNewMessage()
     * @apiGroup AdminMessageController
     * @apiDescription  Save new mail template
     * @apiParam {object} request Data of request
     * @apiSuccess {object} object Object of MailTemplate
     */
    public function saveNewMessage(Request $request){
        $mail_template = new MailTemplate();
        $mail_template = $this->saveMailTemplateDB($request, $mail_template);

        $this->saveMailToGroupDB($request, $mail_template);
        $this->saveMailToUserDB($request, $mail_template);

       return $mail_template;
    }

    /** @api {private} saveNewSingleMessage() saveNewSingleMessage()
     * @apiName saveNewSingleMessage()
     * @apiGroup AdminMessageController
     * @apiDescription  Save new single mail
     * @apiParam {object} request Data of request
     * @apiSuccess {object} object Object of SingleMessage
     */
    public function saveNewSingleMessage(Request $request){
        $mail_single = new SingleMessage();
        $mail_single = $this->saveSingleMailDB($request, $mail_single);

        $this->saveMailSingleToUserDB($request, $mail_single);
        $this->saveMailSingleToGroupDB($request, $mail_single);

       return SingleMessage::find($mail_single->id);
    }

    /** @api {private} saveEditMailTemplate() saveEditMailTemplate()
     * @apiName saveEditMailTemplate()
     * @apiGroup AdminMessageController
     * @apiDescription  Save editing mail template
     * @apiParam {object} request Data of request
     * @apiSuccess {object} object Object of MailTemplate
     */
    public function saveEditMailTemplate(Request $request, $id){
        $mail_template = MailTemplate::where('id', $id)->first();
        $mail_template = $this->saveMailTemplateDB($request, $mail_template);

        MailToGroup::where('mail_template_id',$mail_template->id)->delete();
        MailToUser::where('mail_template_id',$mail_template->id)->delete();

        $this->saveMailToGroupDB($request, $mail_template);
        $this->saveMailToUserDB($request, $mail_template);

       return $mail_template;
    }

    /** @api {public} getSingleDispatchView() getSingleDispatchView()
     * @apiName getSingleDispatchView()
     * @apiGroup AdminMessageController
     * @apiDescription  Get page with one time dispatch messages data.
     */
    public function getSingleDispatchView() {
        $this->data['base_templates'] = BaseMessageTemplate::all();
        $this->data['user_groups'] = UserGroup::all();
        $this->data['users'] = User::all();
        $this->data['variables'] = MessageSpecialeDate::where('id','<=',5)->get();
        $this->data['single_mails'] = SingleMessage::all();

        return view('admin.pages.messages.single_dispatch', $this->data);
    }

    /** @api {public} deleteAllMail() deleteAllMail()
     * @apiName deleteAllMail()
     * @apiGroup AdminMessageController
     * @apiDescription  Delete all mails
     * @apiSuccess {bool} status Operations status
     * @apiSuccessExample Success-Response:
     *  ['status' => 1]
     */
    public function deleteAllMail(){
        MailTemplate::where('id', '>', 0)->delete();
        MailToGroup::where('id', '>', 0)->delete();
        MailToUser::where('id', '>', 0)->delete();

        return ['status'=>1];
    }

    /** @api {public} deleteAllSingleMail() deleteAllSingleMail()
     * @apiName deleteAllSingleMail()
     * @apiGroup AdminMessageController
     * @apiDescription  Delete all single mails
     * @apiSuccess {bool} status Operations status
     * @apiSuccessExample Success-Response:
     *  ['status' => 1]
     */
    public function deleteAllSingleMail(){
        SingleMessage::where('id', '>', 0)->delete();
        SingleMailToGroup::where('id', '>', 0)->delete();
        SingleMailToUser::where('id', '>', 0)->delete();

        return ['status'=>1];
    }

    /** @api {public} draftAllMail() draftAllMail()
     * @apiName draftAllMail()
     * @apiGroup AdminMessageController
     * @apiDescription  Update status of all mail templates
     * @apiSuccess {objects_array} array All objects of MailTemplate
     */
    public function draftAllMail(){
        MailTemplate::where('id', '>', 0)->update(['status'=>0]);

        return MailTemplate::all();
    }

    /** @api {public} draftAllSingleMail() draftAllSingleMail()
     * @apiName draftAllSingleMail()
     * @apiGroup AdminMessageController
     * @apiDescription  Update status of all single mail templates
     * @apiSuccess {objects_array} array All objects of SingleMessage
     */
    public function draftAllSingleMail(){
        SingleMessage::where('id', '>', 0)->where('is_sent',0)->update(['status'=>0]);

        return SingleMessage::all();
    }

    /** @api {public} activeAllSingleMail() activeAllSingleMail()
     * @apiName activeAllSingleMail()
     * @apiGroup AdminMessageController
     * @apiDescription  Update status of all single mail templates
     * @apiSuccess {objects_array} array All objects of SingleMessage
     */
    public function activeAllSingleMail(){
        SingleMessage::where('id', '>', 0)->where('is_sent',0)->update(['status'=>1]);

        return SingleMessage::all();
    }

    /** @api {public} draftAllMail() draftAllMail()
     * @apiName draftAllMail()
     * @apiGroup AdminMessageController
     * @apiDescription  Update status of all mail templates
     * @apiSuccess {objects_array} array All objects of MailTemplate
     */
    public function activeAllMail(){
        MailTemplate::where('id', '>', 0)->update(['status'=>1]);

        return MailTemplate::all();
    }

    /** @api {public} selectedDraftMail() selectedDraftMail()
     * @apiName selectedDraftMail()
     * @apiGroup AdminMessageController
     * @apiParam {object} request Data of request
     * @apiDescription  Update status of selected mail templates
     * @apiSuccessExample Success-Response:
     *  ['status' => 1]
     */
    public function selectedDraftMail(Request $request){
        MailTemplate::whereIn('id', $request['checked'])->update(['status'=>0]);

        return ['status'=>1];
    }

    /** @api {public} selectedDraftSingleMail() selectedDraftSingleMail()
     * @apiName selectedDraftSingleMail()
     * @apiGroup AdminMessageController
     * @apiParam {object} request Data of request
     * @apiDescription  Update status of selected single mail templates
     * @apiSuccessExample Success-Response:
     *  ['status' => 1]
     */
    public function selectedDraftSingleMail(Request $request){
        SingleMessage::whereIn('id', $request['checked'])->update(['status'=>0]);

        return ['status'=>1];
    }

    /** @api {public} selectedActiveMail() selectedActiveMail()
     * @apiName selectedActiveMail()
     * @apiGroup AdminMessageController
     * @apiParam {object} request Data of request
     * @apiDescription  Update status of selected mail templates
     * @apiSuccessExample Success-Response:
     *  ['status' => 1]
     */
    public function selectedActiveMail(Request $request){
        MailTemplate::whereIn('id', $request['checked'])->update(['status'=>1]);

        return ['status'=>1];
    }

    /** @api {public} selectedActiveSingleMail() selectedActiveSingleMail()
     * @apiName selectedActiveSingleMail()
     * @apiGroup AdminMessageController
     * @apiParam {object} request Data of request
     * @apiDescription  Update status of selected single mail templates
     * @apiSuccessExample Success-Response:
     *  ['status' => 1]
     */
    public function selectedActiveSingleMail(Request $request){
        SingleMessage::whereIn('id', $request['checked'])->update(['status'=>1]);

        return ['status'=>1];
    }

    /** @api {public} selectedDeleteMail() selectedDeleteMail()
     * @apiName selectedDeleteMail()
     * @apiGroup AdminMessageController
     * @apiParam {object} request Data of request
     * @apiDescription  Delete selected mails
     * @apiSuccess {bool} status Operations status
     * @apiSuccessExample Success-Response:
     *  ['status' => 1]
     */
    public function selectedDeleteMail(Request $request){
        MailTemplate::whereIn('id', $request['checked'])->delete();

        return ['status'=>1];
    }

    /** @api {public} selectedDeleteSingleMail() selectedDeleteSingleMail()
     * @apiName selectedDeleteSingleMail()
     * @apiGroup AdminMessageController
     * @apiParam {object} request Data of request
     * @apiDescription  Delete selected single mails
     * @apiSuccess {bool} status Operations status
     * @apiSuccessExample Success-Response:
     *  ['status' => 1]
     */
    public function selectedDeleteSingleMail(Request $request){
        SingleMessage::whereIn('id', $request['checked'])->delete();

        return ['status'=>1];
    }

    /** @api {public} getTemplateMail() getTemplateMail()
     * @apiName getTemplateMail()
     * @apiGroup AdminMessageController
     * @apiParam {int} id Id of template message
     * @apiDescription  Get mails template
     * @apiSuccess {object} object Object of MailTemplate
     */
    public function getTemplateMail($id){
        return MailTemplate::where('id', $id)->with('users.user', 'groups.userGroup', 'action')->first();
    }

    /** @api {public} getTemplateSingleMail() getTemplateSingleMail()
     * @apiName getTemplateSingleMail()
     * @apiGroup AdminMessageController
     * @apiParam {int} id Id of template message
     * @apiDescription  Get single mails template
     * @apiSuccess {object} object Object of SingleMessage
     */
    public function getTemplateSingleMail($id){
        return SingleMessage::where('id', $id)->with('users.user', 'groups.userGroup')->first();
    }

    /** @api {public} repeatMailSingle() repeatMailSingle()
     * @apiName repeatMailSingle()
     * @apiGroup AdminMessageController
     * @apiParam {int} id Id of template message
     * @apiParam {object} request Data of request
     * @apiParam {string} request.date Date for send
     * @apiParam {string} request.time Time for send
     * @apiDescription  Repeat single mail
     * @apiSuccess {object} object Object of SingleMessage
     */
    public function repeatMailSingle(Request $request, $id){
        $message = SingleMessage::where('id', $id)->with('users.user', 'groups.userGroup')->first();

        $dispatch_time = $this->getDataString($request);

        $new_message = new SingleMessage();

        $new_message->title         = $message->title;
        $new_message->content       = $message->content;
        $new_message->note_type     = $message->note_type;
        $new_message->dispatch_time = $dispatch_time;
        $new_message->base_template = $message->base_template;
        $new_message->timing_sent   = isset($request['date'])&&isset($request['time']);

        $new_message->save();

        foreach ($message->users as $user){
            $users_DB = new SingleMailToUser();

            $users_DB->single_message_id = $new_message->id;
            $users_DB->user_id = $user->user_id;

            $users_DB->save();
        }

        foreach ($message->groups as $group){
            $users_DB = new SingleMailToUser();

            $users_DB->single_message_id = $new_message->id;
            $users_DB->user_group_id = $group->user_group_id;

            $users_DB->save();
        }

        return  SingleMessage::find($new_message->id);
    }

    /** @api {public} saveEditMailSingle() saveEditMailSingle()
     * @apiName saveEditMailSingle()
     * @apiGroup AdminMessageController
     * @apiParam {int} id Id of single mail
     * @apiParam {object} request Data of request
     * @apiDescription  Save edited single mail
     * @apiSuccess {object} object Object of SingleMessage
     */
    public function saveEditMailSingle(Request $request, $id){
        $mail_single = SingleMessage::where('id', $id)->first();
        $mail_single = $this->saveSingleMailDB($request, $mail_single);

        $this->saveMailSingleToUserDB($request, $mail_single);
        $this->saveMailSingleToGroupDB($request, $mail_single);

        return SingleMessage::find($mail_single->id);
    }
}
