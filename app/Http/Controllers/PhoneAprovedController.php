<?php

namespace App\Http\Controllers;

use App\ApprovedPhone;
use Illuminate\Http\Request;

/**@api PhoneAprovedController
 * @apiName PhoneAprovedController
 * @apiGroup PhoneAprovedController
 * @apiDescription App\Http\Controllers Class PhoneAprovedController
 */
class PhoneAprovedController extends Controller
{

    private $SMS_URL;
    private $SMS_UN;
    private $SMS_PWD;
    private $SMS_API_KEY;
    private $SMS_SENDER;
    private $SMS_COUNTRY_ID;

    /** @api {public} _constructor() _constructor()
     * @apiName _constructor()
     * @apiGroup PhoneAprovedController
     * @apiDescription PhoneAprovedController constructor. Initializes the  data for sending SMS
     */
    public function __construct()
    {
        $this->SMS_API_KEY    = env('SMS_API_KEY');
        $this->SMS_PWD        = env('SMS_PWD');
        $this->SMS_UN         = env('SMS_UN');
        $this->SMS_URL        = env('SMS_URL');
        $this->SMS_SENDER     = env('SMS_SENDER');
        $this->SMS_COUNTRY_ID = env('SMS_COUNTRY_ID');
    }

    /** @api {private} saveNewCode() saveNewCode()
     * @apiName saveNewCode()
     * @apiGroup PhoneAprovedController
     * @apiParam {int} id_model Id element in model for which code is generated
     * @apiParam {string} model Name of model.
     * @apiParam {string} phone Phone of user for who code is generate
     * @apiParam {int} user_id Users id
     * @apiDescription  Generate and saved code for sending in SMS. Use for approve phone and etc.
     * @apiSuccess {object} code ApprovedPhone object.
     */
    private function saveNewCode($id_model, $model, $phone, $user_id){
        $codes_all = ApprovedPhone::all()->pluck('code')->toArray();

        ApprovedPhone::where('model_name',$model)->where('element_id',$id_model)
            ->where('user_id',$user_id)->where('phone_number',$phone)->delete();

        do{
            $code_val = rand(100000, 999999);
        }while(in_array($code_val, $codes_all));

        $code = new ApprovedPhone();

        $code->model_name   = $model;
        $code->element_id   = $id_model;
        $code->user_id      = $user_id;
        $code->phone_number = $phone;
        $code->code         = $code_val;

        $code->save();

        return $code;
    }

    /** @api {private} sendMessage() sendMessage()
     * @apiName sendMessage()
     * @apiGroup PhoneAprovedController
     * @apiParam {string} message message for sending.
     * @apiParam {string} phone Phone of user for who code is generate
     * @apiDescription  Send SMS to user.
     * @apiSuccess {string} output The result of sending.
     */
    private function sendMessage($phone, $message){
        $url="https://control.msg91.com/api/sendhttp.php?authkey=$this->SMS_API_KEY&mobiles=$phone&message=$message&sender=$this->SMS_SENDER&route=default";

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        ));

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $output = curl_exec($ch);

        if(curl_errno($ch))
        {
            return 'error';
        }

        curl_close($ch);

        return $output;
    }

    /** @api {public} deleteOldCode() deleteOldCode()
     * @apiName deleteOldCode()
     * @apiGroup PhoneAprovedController
     * @apiDescription Removes codes older than an hour.
     */
    public function deleteOldCode(){
        $time = time()-(2*60*60);
        ApprovedPhone::where('created_at', '<', date('Y-m-d H:i:s', $time))->delete();
    }

    /** @api {public} setMessage() setMessage()
     * @apiName setMessage()
     * @apiGroup PhoneAprovedController
     * @apiParam {int} id_model Id element in model for which code is generated
     * @apiParam {string} model Name of model.
     * @apiParam {string} phone Phone of user for who code is generate
     * @apiParam {int} user_id Users id
     * @apiDescription  Generate code and send SMS to user.
     * @apiSuccess {bool} status Status of sending message.
     */
    public function setMessage($id_model, $user_id, $model, $phone){
        $code = $this->saveNewCode($id_model, $model, $phone, $user_id);
        $API_code = $this->sendMessage($phone, $code->code);

        if($API_code != 'error'){
            $code->API_code =   $API_code;
            $code->save();

            return true;
        }

        return false;
    }

    /**
     * @param $code
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function getAboutCode($code){
        return ApprovedPhone::where('code', $code)->first();
    }

    /**
     * @param $id
     */
    public function deleteApprovedMessage($id){
        ApprovedPhone::where('id', $id)->delete();
    }

    /** @api {public} setMessage() setMessage()
     * @apiName setMessage()
     * @apiGroup PhoneAprovedController
     * @apiParam {int} id_model Id element in model for which code is generated
     * @apiParam {string} model Name of model.
     * @apiParam {string} phone Phone of user for who code is generate
     * @apiParam {int} user_id Users id
     * @apiParam {string} code Users code for approved phone
     * @apiDescription  Verifies the validity of the user code
     * @apiSuccess {int} status Status of validity code. 200 - valid, 0 - not valid
     */
    public function checkCode($id_model, $user_id, $model, $phone, $code){
        $code_obj = ApprovedPhone::where('model_name',$model)->where('element_id',$id_model)
                             ->where('user_id',$user_id)->where('phone_number',$phone)->first();

        if(count($code_obj) && $code_obj->code == $code){
            ApprovedPhone::where('id', $code_obj->id)->delete();

            return 200;
        } else {
            return 0;
        }
    }
}
