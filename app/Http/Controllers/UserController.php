<?php

namespace App\Http\Controllers;

use App\PaymentOptions;
use App\Role;
use App\User;
use App\UserGroup;
use App\UserProfile;
use App\UsersAddress;
use App\UsersFile;
use Faker\Provider\UserAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;
use Validator;
use Illuminate\Support\Facades\Auth;

/**@api UserController
 * @apiName UserController
 * @apiGroup UserController
 * @apiDescription App\Http\Controllers Class UserController
 */
class UserController extends BaseController
{
    /** @api {public} loginUser() loginUser()
     * @apiName loginUser()
     * @apiGroup UserController
     * @apiDescription  Return view login and register.
     */
    public function loginUser(){
        if(Auth::user()){
            return redirect()->route('home');
        }

        return view('client.pages.users.signin-signup');
    }

    /** @api {public} profile() profile()
     * @apiName profile()
     * @apiGroup UserController
     * @apiDescription  Return view users profile.
     */
    public function profile(){
        self::userIsAuth();

        $this->data['user'] =  User::where('id', Auth::id())->with('group', 'avatar', 'profile', 'files', 'organization', 'organization.companyType', 'group.baseGroup', 'profile.avatar')->first();

        return view('client.pages.users.my-account', $this->data);
    }

    /** @api {public} getChangePassword() getChangePassword()
     * @apiName getChangePassword()
     * @apiGroup UserController
     * @apiDescription  Return view change password for user.
     */
    public function getChangePassword(){
        self::userIsAuth();
        return view('client.pages.users.change-password', $this->data);
    }

    /** @api {private} validateChangePassword() validateChangePassword()
     * @apiName validateChangePassword()
     * @apiGroup UserController
     * @apiParam {object} request Request data
     * @apiParam {string} request.password_old Old password
     * @apiParam {string} request.password_new New password
     * @apiParam {string} request.password_new_confirmation New password
     * @apiDescription  Validate forms data for change users password.
     * @apiSuccess {object} validate Validator object with result of validation
     */
    private function validateChangePassword(Request $request){
        $validate = [
            'password_old' => 'required|min:8|max:255',
            'password_new' => 'required|min:8|max:255|confirmed'
        ];
        return Validator::make($request->all(), $validate);
    }

    /** @api {private} validateChangePasswordFB() validateChangePasswordFB()
     * @apiName validateChangePasswordFB()
     * @apiGroup UserController
     * @apiParam {object} request Request data
     * @apiParam {string} request.password_new New password
     * @apiParam {string} request.password_new_confirmation New password
     * @apiDescription  Validate forms data for change users password. If user sing up from facebook
     * @apiSuccess {object} validate Validator object with result of validation
     */
    private function validateChangePasswordFB(Request $request){
        $validate = [
            'password_new' => 'required|min:8|max:255|confirmed'
        ];
        return Validator::make($request->all(), $validate);
    }

    /** @api {public} changePassword() changePassword()
     * @apiName changePassword()
     * @apiGroup UserController
     * @apiParam {object} request Request data
     * @apiParam {string} request.password_old Old password. Optional(for FB)
     * @apiParam {string} request.password_new New password
     * @apiParam {string} request.password_new_confirmation New password
     * @apiDescription  Change users password. Redirect to profile page
     */
    public function changePassword(Request $request){
        self::userIsAuth();
        $user = Auth::user();

        if ($user->facebook){
            $validator = $this->validateChangePasswordFB($request);
        } else{
            $validator = $this->validateChangePassword($request);
        }

        if($validator->fails()){
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $user->password = bcrypt($request['password_new']);
        $user->save();

        return redirect()->route('auth_profile', $this->data);
    }

//    validation users data

    /** @api {public} validateUser() validateUser()
     * @apiName validateUser()
     * @apiGroup UserController
     * @apiParam {object} request Request data
     * @apiParam {string} request.firstName Users first name
     * @apiParam {string} request.lastName Users last name
     * @apiParam {email} request.email Users email
     * @apiParam {int} request.group Users group id. Optional
     * @apiParam {string} request.terms Users status term. Optional
     * @apiParam {string} request.phone Users phone. Optional
     * @apiParam {string} request.officePhone Users office phone. Optional
     * @apiDescription  Validate forms data for added user.
     * @apiSuccess {object} validate Validator object with result of validation
     */
    public function validateUser($request){
        $validate_admin = [
            'firstName' => 'required|max:255',
            'lastName' => 'required|max:255',
            'email' => 'required|unique:users|email'
        ];

        $validate_user = array_merge($validate_admin, [
            'group' => 'required|min:1|max:5'
        ]);

        $validate_general_user = array_merge($validate_admin, [
            'terms' => 'required'
        ]);

        $validate_designer_user = array_merge($validate_admin, [
            'phone' => 'required|size:12|string'
        ]);

        $validate_SME_user = array_merge($validate_general_user, [
            'officePhone' => 'required|size:12|string',
            'phone' => 'required|size:12|string'
        ]);

        $validate_vendor_user = array_merge($validate_designer_user, [
            'officePhone' => 'required|size:12|string'
        ]);

        $group_id = $request['group'];
        $group = UserGroup::where('id',$group_id)->with('baseGroup')->first();

        if($group->baseGroup->id == 1){
            $validate = $validate_general_user;
        } elseif ($group->baseGroup->id == 5){
            $validate = $validate_admin;
        } elseif ($group->baseGroup->id == 2){
            $validate = $validate_SME_user;
        } elseif ($group->baseGroup->id == 3){
            $validate = $validate_vendor_user;
        } elseif ($group->baseGroup->id == 4){
            $validate = $validate_designer_user;
        } else {$validate = $validate_user;}

        return Validator::make($request->all(), $validate);
    }

    //    validation users data for update

    /** @api {public} validateUserUpdate() validateUserUpdate()
     * @apiName validateUserUpdate()
     * @apiGroup UserController
     * @apiParam {object} request Request data
     * @apiParam {string} request.firstName Users first name
     * @apiParam {string} request.lastName Users last name
     * @apiParam {int} request.group Users group id. Optional
     * @apiParam {string} request.terms Users status term. Optional
     * @apiParam {string} request.phone Users phone. Optional
     * @apiParam {string} request.officePhone Users office phone. Optional
     * @apiDescription  Validate forms data for update users data.
     * @apiSuccess {object} validate Validator object with result of validation
     */
    public function validateUserUpdate($request){
        $validate_general_user = [
            'firstName' => 'required|max:255',
            'lastName' => 'required|max:255',
        ];

        $validate_user = array_merge($validate_general_user, [
            'group' => 'required|min:1|max:5'
        ]);

        $validate_designer_user = array_merge($validate_general_user, [
            'phone' => 'required|size:12|string'
        ]);

        $validate_SME_vendor_user = array_merge($validate_designer_user, [
            'officePhone' => 'required|size:12|string'
        ]);

        $group_id = $request['group'];
        $group = UserGroup::where('id',$group_id)->with('baseGroup')->first();

        if($group->baseGroup->id == 1|| $group->baseGroup->id == 5){
            $validate = $validate_general_user;
        } elseif ($group->baseGroup->id == 2||$group->baseGroup->id == 3){
            $validate = $validate_SME_vendor_user;
        } elseif ($group->baseGroup->id == 4){
            $validate = $validate_designer_user;
        } else {$validate = $validate_user;}

        return Validator::make($request->all(), $validate);
    }

//    create file

    /** @api {public} saveAvatar() saveAvatar()
     * @apiName saveAvatar()
     * @apiGroup UserController
     * @apiParam {object} request Request data
     * @apiParam {file} request.avatar Users avatars file
     * @apiParam {object} user User object
     * @apiDescription  Save users avatar file. Return id new file in UsersFile model
     * @apiSuccess {int} id Id new file in UsersFile model
     */
    public function saveAvatar(Request $request, $user){
        $users_files = $request->file('avatar')->store('public/avatars');
        $users_files = substr($users_files,6,strlen($users_files)-1);

        return $this->setUserFile($users_files, 'Avatar', $user->id, 1);
    }

    /** @api {public} userEmailToken() userEmailToken()
     * @apiName userEmailToken()
     * @apiGroup UserController
     * @apiParam {object} request Request data
     * @apiParam {string} request.email Users email
     * @apiDescription  Checks the presence of the email in User model
     * @apiSuccess {int} status Status presence email. If email is absent - 0 else 1. If mail invalid - -1.
     */
    public function userEmailToken(Request $request){
        if(isset($request['email'])&& strlen($request['email'])>4){
            $email_count = User::where('email', $request['email'])->count();

            if($email_count>0){
                return ['email'=>1];
            } else{
                return ['email'=>0];
            }
        }
        return ['email'=>-1];
    }

    /** @api {public} setUserFile() setUserFile()
     * @apiName setUserFile()
     * @apiGroup UserController
     * @apiParam {string} file_id File url
     * @apiParam {string} file_title Title for file
     * @apiParam {int} user_id User id
     * @apiParam {int} type_file Type file
     * @apiDescription  Save file data to UsersFile model
     * @apiSuccess {int} id File id in UsersFile model
     */
    public function setUserFile($file_id, $file_title, $user_id, $type_file){
        $file = new UsersFile();

        $file->url = $file_id;
        $file->title = $file_title;
        $file->user_id = $user_id;
        $file->type_file_id = $type_file;

        $file->save();

        return $file->id;
    }

    /** @api {public} saveUserDocument() saveUserDocument()
     * @apiName saveUserDocument()
     * @apiGroup UserController
     * @apiParam {object} request Request data
     * @apiParam {files_array} request.files Users files
     * @apiParam {object} user User object
     * @apiDescription  Save users files.
     * @apiSuccess {bool} status Status of saving
     */
    public function saveUserDocument(Request $request, $user){
        foreach ($request->file('files') as $file){
            $file_url = $file->store('public/documents/user_'.$user->id);
            $file_url = substr($file_url,6,strlen($file_url)-1);
            $id = $this->setUserFile($file_url, 'Document SME', $user->id, 2);
        }
        return true;
    }

    /** @api {public} delUserAvatar() delUserAvatar()
     * @apiName delUserAvatar()
     * @apiGroup UserController
     * @apiDescription  Delete users avatar
     * @apiParam {int} id Users id
     * @apiSuccessExample Success-Response:
     *  back()
     */
    public function delUserAvatar($id){

        $user_file = UsersFile::where('user_id', $id)->where('type_file_id',1)->first();
        Storage::delete($user_file->url);
        $user_file = UsersFile::find($user_file->id)->delete();
        $user_profile = UserProfile::where('user_id',$id)->update(['users_files_id'=>0]);

        return back();
    }

    /** @api {public} deleteUserFile() deleteUserFile()
     * @apiName deleteUserFile()
     * @apiGroup UserController
     * @apiDescription  Delete users file
     * @apiParam {int} id File id
     * @apiSuccessExample Success-Response:
     *  ['status'=>'1']
     */
    public function deleteUserFile($id){
        $user_file = UsersFile::find($id);
        Storage::delete($user_file->url);
        $user_file->delete();

        return ['status'=>'1'];
    }

    /** @api {public} getSavedAddresses() getSavedAddresses()
     * @apiName getSavedAddresses()
     * @apiGroup UserController
     * @apiDescription  Return view with users addresses.
     */
    public function getSavedAddresses(){
        self::userIsAuth();

        $user = Auth::user();
        $GeolocationController = new GeolocationController();

        $this->data['countries'] = $GeolocationController->getCountrys();
        $this->data['addresses'] = $user->addresses()->get();

        return view('client.pages.users.saved-address', $this->data);
    }

    /** @api {public} addressValidate() addressValidate()
     * @apiName addressValidate()
     * @apiGroup UserController
     * @apiParam {object} request Request data
     * @apiParam {string} request.firstName Users first name
     * @apiParam {string} request.lastName Users last name
     * @apiParam {int} request.address Users address
     * @apiParam {string} request.zipCode Users zipCode
     * @apiParam {string} request.phone Users phone. Optional
     * @apiDescription  Validate forms data users address.
     * @apiSuccess {object} validate Validator object with result of validation
     */
    private function addressValidate(Request $request){
        $validate = [
            'firstName' => 'required|max:255|min:2',
            'lastName' => 'required|max:255|min:2',
            'address' => 'required|max:255|min:2',
            'zipCode' => 'required|size:6|string',
            'phone' => 'nullable|alpha_dash|size:12|string',
        ];

        return Validator::make($request->all(), $validate);
    }

    /** @api {public} deleteUserAddresses() deleteUserAddresses()
     * @apiName deleteUserAddresses()
     * @apiGroup UserController
     * @apiDescription  Delete users address
     * @apiParam {int} id Users address id
     * @apiSuccessExample Success-Response:
     *  back()
     */
    public function deleteUserAddresses($id){
        UsersAddress::where('id', $id)->where('user_id',Auth::id())->delete();

        return back();
    }

    /** @api {public} getAddressData() getAddressData()
     * @apiName getAddressData()
     * @apiGroup UserController
     * @apiParam {int} id Users address id
     * @apiDescription  Get user address data
     * @apiSuccess {object} address Object UsersAddress
     */
    public function getAddressData($id){
        return UsersAddress::where('id', $id)->where('user_id',Auth::id())->first();
    }

    /** @api {public} saveEditSavedAddress() saveEditSavedAddress()
     * @apiName saveEditSavedAddress()
     * @apiGroup UserController
     * @apiDescription  Update users address data
     * @apiParam {object} request Request data
     * @apiParam {string} request.firstName Users first name
     * @apiParam {string} request.lastName Users last name
     * @apiParam {int} request.address Users address line 1
     * @apiParam {int} request.city Users city code
     * @apiParam {int} request.state Users state code
     * @apiParam {int} request.country Users country code
     * @apiParam {int} request.address_line_2 Users address line 2
     * @apiParam {string} request.zipCode Users zipCode
     * @apiParam {string} request.phone Users phone. Optional
     * @apiSuccessExample Success-Response:
     *  back()
     */
    public function saveEditSavedAddress(Request $request){
        $validator = $this->addressValidate($request);
        if($validator->fails()){
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $geo = new GeolocationController();

        $getCite=isset($request['city'])?$geo->getCite($request['city']):'';
        $getState=isset($request['state'])?$geo->getState($request['state']):'';
        $getCountry=isset($request['country'])?$geo->getCountry($request['country']):'';

        $address = UsersAddress::where('id', $request['id'])->where('user_id', Auth::id())->first();

        $address->first_name    = $request['firstName'];
        $address->last_name     = $request['lastName'];
        $address->city_id       = isset($request['city'])           ? $getCite['code']           :'';
        $address->city_name     = isset($request['city'])           ? $getCite['name']           :'';
        $address->state_id      = isset($request['state'])          ? $getState['code']          :'';
        $address->state_name    = isset($request['state'])          ? $getState['name']          :'';
        $address->country_id    = isset($request['country'])        ? $getCountry['code']        :'';
        $address->country_name  = isset($request['country'])        ? $getCountry['name']        :'';
        $address->street_line_1 = isset($request['address']) ? $request['address'] :'';
        $address->street_line_2 = isset($request['address_line_2']) ? $request['address_line_2'] :'';
        $address->zipcode       = isset($request['zipCode'])       ? $request['zipCode']       :'';

        if(isset($request['phone']) && $address->phone != $request['phone']){
            $address->phone         = isset($request['phone'])   ? $request['phone']   :'';
            $address->phone_approved= false;
        }

        $address->save();

        return back();
    }

    /** @api {public} saveSavedAddress() saveSavedAddress()
     * @apiName saveSavedAddress()
     * @apiGroup UserController
     * @apiDescription  save new users address
     * @apiParam {object} request Request data
     * @apiParam {string} request.firstName Users first name
     * @apiParam {string} request.lastName Users last name
     * @apiParam {int} request.address Users address line 1
     * @apiParam {int} request.city Users city code
     * @apiParam {int} request.state Users state code
     * @apiParam {int} request.country Users country code
     * @apiParam {int} request.address_line_2 Users address line 2
     * @apiParam {string} request.zipCode Users zipCode
     * @apiParam {string} request.phone Users phone. Optional
     * @apiSuccessExample Success-Response:
     *  back()
     */
    public function saveSavedAddress(Request $request){
        self::userIsAuth();

        $validator = $this->addressValidate($request);

        if($validator->fails()){
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $geo = new GeolocationController();

        $getCite=isset($request['city'])?$geo->getCite($request['city']):'';
        $getState=isset($request['state'])?$geo->getState($request['state']):'';
        $getCountry=isset($request['country'])?$geo->getCountry($request['country']):'';

        $address = new UsersAddress();

        $address->user_id       = Auth::id();
        $address->first_name    = $request['firstName'];
        $address->last_name     = $request['lastName'];
        $address->city_id       = isset($request['city'])           ? $getCite['code']           :'';
        $address->city_name     = isset($request['city'])           ? $getCite['name']           :'';
        $address->state_id      = isset($request['state'])          ? $getState['code']          :'';
        $address->state_name    = isset($request['state'])          ? $getState['name']          :'';
        $address->country_id    = isset($request['country'])        ? $getCountry['code']        :'';
        $address->country_name  = isset($request['country'])        ? $getCountry['name']        :'';
        $address->street_line_1 = isset($request['address']) ? $request['address'] :'';
        $address->street_line_2 = isset($request['address_line_2']) ? $request['address_line_2'] :'';
        $address->zipcode       = isset($request['zipCode'])       ? $request['zipCode']       :'';
        $address->phone         = isset($request['phone'])   ? $request['phone']   :'';

        $address->save();

        return back();
    }

    /**
 * @param $id
 */
    public function deleteSavedAddress($id){
        self::userIsAuth();


    }
    private function validateUserData(Request $request){

    }

    /** @api {public} updateUserData() updateUserData()
     * @apiName updateUserData()
     * @apiGroup UserController
     * @apiDescription  Update users data
     * @apiParam {object} request Request data
     * @apiParam {string} request.firstName Users first name
     * @apiParam {string} request.lastName Users last name
     * @apiParam {string} request.email Users email
     * @apiParam {string} request.phone Users phone. Optional
     * @apiSuccessExample Success-Response:
     *  back()
     */
    public function updateUserData(Request $request){
        $user = Auth::user();

        $validate_arr = [
            'firstName' => 'required|min:2|max:255',
            'lastName' => 'required|min:2|max:255'
        ];

        if($user->email != $request['email']){
            $validate_arr ['email'] ='required|unique:users|mail';
        }

        $user_group = $user->group()->first();
        if($user_group->base_group_id > 2 &&  $user_group->base_group_id <6 || $request->has('phone')){
            if($user->phone != $request['phone']){
                $validate_arr ['phone'] ='required|digits:12';
            }
        }

        $validator = Validator::make($request->all(), $validate_arr);

        if($validator->fails()){
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $user->first_name = $request['firstName'];
        $user->last_name = $request['lastName'];

        if($request->has('phone') && $user->phone != $request['phone']){
            $user->phone = $request['phone'];
            $user->phone_confirmed = false;
        }

        if($user->email != $request['email']){
            $user->email = $request['email'];
            $user->mail_confirmed = false;
        }

        $user->save();

        return back();
    }

    /** @api {public} getConfirmPhone() getConfirmPhone()
     * @apiName getConfirmPhone()
     * @apiGroup UserController
     * @apiDescription  Return view with confirmed phone.
     */
    public function getConfirmPhone(){
        return view('client.pages.users.confirm-phone', $this->data);
    }

    /** @api {public} postConfirmPhone() postConfirmPhone()
     * @apiName postConfirmPhone()
     * @apiGroup UserController
     * @apiDescription  send SMS for Authentication User Phone
     * @apiSuccess {bool} status Sending status
     * @apiSuccessExample Success-Response:
     *  ['status' => 1]
     */
    public function postConfirmPhone(){
        $sms = new PhoneAprovedController();
        $user = Auth::user();
        $sms->setMessage($user->id, $user->id, 'User', $user->phone);

        return ['status' => 1];
    }

    /** @api {public} postConfirmPhoneCheck() postConfirmPhoneCheck()
     * @apiName postConfirmPhoneCheck()
     * @apiGroup UserController
     * @apiDescription  User Phone Authentication
     * @apiSuccess {bool} code Confirmed status.
     * @apiSuccess {string} message Message of status
     * @apiSuccessExample Success-Response:
     *  [
     *      'code' => 200,
     *      'message' => 'Phone is confirmed.'
     * ]
     */
    public function postConfirmPhoneCheck(Request $request){
        if($request->has('password')){
            $sms = new PhoneAprovedController();
            $user = Auth::user();

            $code = $sms->checkCode($user->id, $user->id, 'User', $user->phone, $request['password']);

            if($code == 200){
                $user->phone_confirmed = true;
                $user->save();

                return ['code' => 200, 'message' => 'Phone is confirmed.'];
            }

            return ['code' => 0, 'message' => 'Code is invalid.'];
        }

        return ['code' => 0, 'message' => 'Code is required.'];
    }







    // Update users mail.

    /**
     * @param Request $request
     * @param $id
     * @return $this|bool
     */
    public function updateMail(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users|mail'
        ]);

        if($validator->fails()){
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::where('id', $id)->update(['email'=>$request['email']]);

        return $user;
    }

    // Update users name.

    /**
     * @param Request $request
     * @param $id
     * @return $this|bool
     */
    public function updateName(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255'
        ]);

        if($validator->fails()){
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::where('id', $id)->update(['name'=> $request['name']]);

        return $user;
    }

    //Delete user.

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id){
        User::where('id', $id)->delete();
        UserProfile::where('user_id', $id)->delete();
        return back();
    }

    // Delete Users

    /**
     * @param Request $request
     * @return bool|null
     */
    public function deleteUsers(Request $request){
        User::whereIn('id', $request['users'])->delete();
        return UserProfile::whereIn('user_id', $request['users'])->delete();
    }

    //Get roles user by role name

    /**
     * @param $role
     * @return mixed
     */
    public function getUsersRole($role){
        $role = Role::where('title', $role);

        return $this->getUsersRoleId($role->id);
    }

    //Get roles user by role id

    /**
     * @param $id
     * @return mixed
     */
    public function getUsersRoleId($id){
        return Role::find($id)->users();
    }

    //Get all users

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllUsers(){
        return User::all();
    }

    //Get approved users

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getApprovedUsers(){
        return User::where('approval', true)->get();
    }

    //Get user with users data

    /**
     * @param $id
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function getUser($id){
        return User::find($id)->with('profile', 'role');
    }

    //Approval user or users by id

    /**
     * @param Request $request
     * @return bool
     */
    public function approvalUser(Request $request){
        return User::whereIn('id',$request['users_id'])->update('approval', true);
    }

    //Approval user or users by role id

    /**
     * @param Request $request
     * @return $this|bool
     */
    public function approvalUserRole(Request $request){
        $role_max = Role::max('id');

        $validator = Validator::make($request->all(), [
            'group_id' =>"required|min:0|max:$role_max"
        ]);

        if($validator->fails()){
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        return User::where('approval', false)->where('group_id', $request['group_id'])->update('approval', true);
    }

    //Get not approved users

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getNewUsers(){
        return User::where('approval', false)->get();
    }
}
