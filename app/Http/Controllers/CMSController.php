<?php

namespace App\Http\Controllers;

use App\PagesSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**@api CMSController
 * @apiName CMSController
 * @apiGroup CMSController
 * @apiDescription App\Http\Controllers Class CMSController
 */
class CMSController extends Controller
{

    /** @api {public} getPageAdmin() getPageAdmin()
     * @apiName getPageAdmin()
     * @apiGroup CMSController
     * @apiParam {string} name URL of page
     * @apiDescription  Return view with CMS content for Admin. If CMS page is absent - return 404 error page
     */
    public function getPageAdmin($name){
        $page = PagesSystem::where('url', $name)->first();
        if(count($page)>0){
            return view('cms_pages.layouts.base',['page'=>$page]);
        }else{
            return view('404');
        }
    }

    /** @api {public} getPage() getPage()
     * @apiName getPage()
     * @apiGroup CMSController
     * @apiParam {string} name URL of page
     * @apiDescription  Return view with CMS content for user. If CMS page is absent - return 404 error page
     */
    public function getPage($name){
        $page = PagesSystem::where('url', $name)->where('status_drop_id', 1)->first();
        if(count($page)>0){
            return view('cms_pages.layouts.base',['page'=>$page]);
        }else{
            return view('404');
        }
    }
}
