<?php

namespace App\Http\Controllers;

use App\Category;
use App\InputMessage;
use App\Order;
use App\PopularInquiry;
use App\TimeDispatchMessages;
use App\User;
use App\UserGroup;
use Illuminate\Http\Request;

/**@api AdminBaseController
 * @apiName AdminBaseController
 * @apiGroup AdminBaseController
 * @apiDescription App\Http\Controllers Class AdminBaseController
 */
class AdminBaseController extends Controller
{
    protected $data = [];

    /** @api {public} _constructor() _constructor()
     * @apiName _constructor()
     * @apiGroup AdminBaseController
     * @apiDescription AdminBaseController constructor. Initializes the general data for the group of controllers Admin
     *
     * @apiSuccessExample Success-Response:
     *     $this->data['new']
     */
    public function __construct()
    {
        $this->data['new'] = self::baseData();
    }

    /** @api {public} baseData() baseData()
     * @apiName baseData()
     * @apiGroup AdminBaseController
     * @apiDescription  Forms the general data for the group of controllers Admin
     *
     * @apiSuccessExample Success-Response:
     * $data=[
     *      'user',
     *      'orders',
     *      'prod_groups',
     *      'user_groups',
     *      'new_message',
     *      'month',
     *      'days_week',
     *      'time_settings',
     *      'helps',
     * ]
     */
    public static function baseData(){
        $data=[];

        $data['user'] = User::where('approval',0)->where('group_id', '!=', 6)->count();
        $data['orders'] = Order::where('execution_status',0)->count();
        $data['prod_groups'] = Category::where('id', '>', 0)->get();
        $data['user_groups'] = UserGroup::where('base_group_id','!=', 6)->get();
        $data['new_message'] = InputMessage::where('is_read',0)->where('recipient_user_id',0)->count();
        $data['month'] = ['January','February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $data['days_week'] = ['Monday','Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $data['time_settings'] = TimeDispatchMessages::find(1);
        $data['helps'] = PopularInquiry::all();
        return $data;
    }
}
