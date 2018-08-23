<?php

namespace App\Http\Controllers;

use App\Category;
use App\Product;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


/**@api BaseController
 * @apiName BaseController
 * @apiGroup BaseController
 * @apiDescription App\Http\Controllers Class BaseController
 */
class BaseController extends Controller
{
    protected $data = [];

    /** @api {public} _constructor() _constructor()
     * @apiName _constructor()
     * @apiGroup BaseController
     * @apiDescription BaseController constructor. Initializes the general data for the group of controllers
     *
     * @apiSuccessExample Success-Response:
     *     $this->data['base']
     */
    public function __construct()
    {
        $this->data['base'] = self::getBaseDate();
    }

    /** @api {public} getBaseDate() getBaseDate()
     * @apiName getBaseDate()
     * @apiGroup BaseController
     * @apiDescription  Forms the general data for the group of controllers
     *
     * @apiSuccessExample Success-Response:
     * $data=[
     *      'product_groups',
     *      'new_template',
     *      'best_saller',
     *      'trending',
     * ]
     */
    public static function getBaseDate(){
        $data = [];

        $data['product_groups'] = Category::where('id', '>', 0)->with('categoryImage')->get();
        $data['new_template'] = Product::where('status_active',1)->where('status_approved',1)->where('is_base',0)->orderByDesc('created_at')->limit(12)->with('preview')->get();
        $data['best_saller'] = Product::where('status_active',1)->where('status_approved',1)->where('is_base',0)->orderByDesc('created_at')->limit(12)->with('preview')->get();
        $data['trending'] = Product::where('status_active',1)->where('status_approved',1)->where('is_base',0)->orderByDesc('created_at')->limit(12)->with('preview')->get();

        return $data;
    }

    /** @api {public} userIsAuth() userIsAuth()
     * @apiName userIsAuth()
     * @apiGroup BaseController
     * @apiDescription Verification of user authorization. if user is not authorized - redirect to the login page.
     * @apiSuccessExample Success-Response:
     * true
     */
    public static function userIsAuth(){
        if(Auth::user()){
            return true;
        } else{
            return redirect()->route('user_login_page');
        }
    }
}
