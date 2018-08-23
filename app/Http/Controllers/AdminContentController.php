<?php

namespace App\Http\Controllers;

use App\BaseSEOData;
use App\Category;
use App\ContentImage;
use App\PagesCategories;
use App\PagesCMS;
use App\PagesSystem;
use App\UserGroup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Validator;
use Illuminate\Http\Request;
use DB;

/**@api AdminContentController
 * @apiName AdminContentController
 * @apiGroup AdminContentController
 * @apiDescription App\Http\Controllers Class AdminContentController
 */
class AdminContentController extends AdminBaseController
{
    private $categories_save_id = [];
    private $categories_save    = [];
    private $categories_page    = [];

    /** @api {public} getCategories() getCategories()
     * @apiName getCategories()
     * @apiGroup AdminContentController
     * @apiDescription  Get categories page.
     */
    public function getCategories(){
        return view('admin.pages.categories_pages', $this->data);
    }

    /**
     * @return string
     */
    public function postCategories(){
        $categories     = PagesCategories::all();
        $parents        = $categories->where('parent_id', 0);
        $categories_out = $this->recursiveCategories($parents, $categories);

        return json_encode($categories_out);
    }

    /** @api {public} saveCategories() saveCategories()
     * @apiName saveCategories()
     * @apiGroup AdminContentController
     * @apiDescription  Save category tree.
     * @apiParam {Array} data Tree data
     * @apiSuccess {bool} status Status of operation.
     * @apiSuccessExample Success-Response:
     *  1
     */
    public function saveCategories(Request $request){
        $categories = json_decode($request['data']);

        foreach ($categories as $category) {
            $this->categories_save_id[] = $category->id;
            $this->categories_save[]    = [
                'id' => $category->id,
                'name' => $category->text,
                'parent_id' => 0,
            ];

            if (isset($category->children)) {
                $this->recursiveDecodeCategories($category->id, $category->children);
            }
        }
        $delete_categories = PagesCategories::whereNotIn('id', $this->categories_save_id)->get();
        PagesCategories::whereNotIn('id', $this->categories_save_id)->delete();
        $delete_categories = $delete_categories->pluck('id')->toArray();
        PagesSystem::whereIn('category_id', $delete_categories)->delete();

        $categories_DB = PagesCategories::all();

        foreach ($this->categories_save as $category_save){
            $category_DB=$categories_DB->where('id', $category_save['id'])->first();
            if (isset($category_DB)){
                if($category_save['name']!=$category_DB->name || $category_save['parent_id']!=$category_DB->parent_id){
                    PagesCategories::where('id',$category_save['id'])->update(['name'=>$category_save['name'], 'parent_id'=>$category_save['parent_id']]);
                }
            } else{
                DB::table('pages_categories')->insert(['name'=>$category_save['name'], 'parent_id'=>$category_save['parent_id']]);
            }

        }

        return 1;
    }

    /**
     * @param $parents
     * @param $categories
     * @return array
     */
    private function recursiveCategories($parents, $categories){
        $categories_out = [];
        $i=0;

        foreach($parents as $parent){
            $categories_out[$i]['id']   = $parent->id;
            $categories_out[$i]['text'] = $parent->name;
            $children                   = $categories->where('parent_id', $parent->id);

            if($children->count()>0){
                $categories_out[$i]['children'] = $this->recursiveCategories($children, $categories);
            }

            $i++;
        }

        return $categories_out;
    }

    /**
     * @param $category
     * @param $categori_id
     * @return array
     */
    private function recursiveCategoriesID($category, $categori_id){
        $first          = $category->where('id', $categori_id)->first();
        $categories_out = [$first->id];
        $chields        = $category->where('parent_id', $categori_id);

        foreach($chields as $chield){
            $categories_out[] = $chield->id;
            $children         = $category->where('parent_id', $chield->id);

            if($children->count()>0){
                $categories_out = array_merge($categories_out,$this->recursiveCategoriesID($category, $chield->id));
            }
        }

        return array_unique($categories_out);
    }

    /**
     * @param $parents
     * @param $categories
     * @return array
     */
    private function recursiveCategoriesText($parents, $categories){
        $categories_out = [];
        $i = 0;

        foreach($parents as $parent){
            $categories_out[$i]['id']   = $parent->id;
            $categories_out[$i]['text'] = $parent->name;
            $children                   = $categories->where('parent_id', $parent->id);

            if($children->count()>0){
                $childrens = $this->recursiveCategoriesText($children, $categories);

                foreach ($childrens as $key=>$children){
                    $childrens[$key]['text'] = $parent->name.'/'.$children['text'];
                }

                $categories_out = array_merge($categories_out, $childrens);
                $i = count($categories_out);
            }
            $i++;
        }

        return $categories_out;
    }

    /**
     * @param $parent_id
     * @param $categories
     */
    private function recursiveDecodeCategories($parent_id, $categories){
        foreach ($categories as $category) {
            $this->categories_save_id[] = $category->id;
            $this->categories_save[]    = [
                'id' => $category->id,
                'name' => $category->text,
                'parent_id' => $parent_id,
            ];

            if (isset($category->children)) {
                $this->recursiveDecodeCategories($category->id, $category->children);
            }
        }
    }

    /** @api {public} getPages() getPages()
     * @apiName getPages()
     * @apiGroup AdminContentController
     * @apiDescription  Get page of category.
     */
    public function getPages(){
        $categories = PagesCategories::all();

        $this->data['pages']       = PagesSystem::all();
        $this->data['categories']  = collect($this->recursiveCategoriesText($categories->where('parent_id',0), $categories));

        return view('admin.pages.cms.pages', $this->data);
    }

    /** @api {public} addPage() addPage()
     * @apiName addPage()
     * @apiGroup AdminContentController
     * @apiDescription  Get added category page.
     */
    public function addPage(){
        $this->data['page_categories'] = PagesCategories::where('parent_id',0)->get();
        $this->data['base_pages']      = PagesSystem::all();
        $this->data['base_seo']        = BaseSEOData::all();

        return view('admin.pages.cms.add_page', $this->data);
    }

    /**
     * @param $parent_id
     * @return \Illuminate\Support\Collection
     */
    public function getSubPageCategories($parent_id){
        return PagesCategories::where('parent_id',$parent_id)->get();
    }

    /** @api {private} validatePage() validatePage()
     * @apiName validatePage()
     * @apiGroup AdminContentController
     * @apiDescription  Validate pages data. For adding
     * @apiParam (Request) {string} title Pages title
     * @apiParam (Request) {string} description Pages description
     * @apiParam (Request) {string} content Pages content
     * @apiParam (Request) {string} category Pages category
     * @apiSuccess Success-Response:
     *  object Validator
     */
    private function validatePage(Request $request){
        $validate = [
            'title' => 'required|max:255|min:3|unique:pages_systems',
            'description' => 'required',
            'url' => 'required|max:255|unique:pages_systems',
            'content' => 'required',
            'category.*' => 'required_with:category.*'
        ];

        return Validator::make($request->all(), $validate);
    }

    /** @api {private} validatePageOld() validatePageOld()
     * @apiName validatePageOld()
     * @apiGroup AdminContentController
     * @apiDescription  Validate pages data. For editing
     * @apiParam (Request) {object} Request Request object
     * @apiSuccess Success-Response:
     *  object Validator
     */
    private function validatePageOld(Request $request){
        $validate = [
            'title'       => 'required|max:255|min:3',
            'description' => 'required',
            'url'         => 'required|max:255',
            'content'     => 'required',
            'category.*'  => 'required_with:category.*'
        ];

        return Validator::make($request->all(), $validate);
    }

    /** @api {private} saveDataPage() saveDataPage()
     * @apiName saveDataPage()
     * @apiGroup AdminContentController
     * @apiDescription  Saving data page
     * @apiParam {object} request Request object
     * @apiParam {object} page Page object
     * @apiSuccess Success-Response:
     *  object Validator
     */
    private function saveDataPage(Request $request, $page){
        $status_drop_name = 'Unpublished';
        if ($request['status_drop'] == 1){
            $status_drop_name = 'Publish';
        }elseif ($request['status_drop'] == 2){
            $status_drop_name = 'Draft';
        }

        $page->title            = $request['title'];
        $page->content          = $request['content'];
        $page->url              = $request['url'];
        $page->status_drop_id   = isset($request['status_drop'])?$request['status_drop']:0;
        $page->status_drop_name = $status_drop_name;
        $page->description      = $request['description'];
        $category               = isset($request['category'])?$request['category']:0;
        $page->category_id      = isset($request['category'])?$category[count($category)-1]:0;
        $page->seo_data      = isset($request['SEO_other_data'])?$request['SEO_other_data']:0;

        $page->save();
    }

    /** @api {public} saveNewPage() saveNewPage()
     * @apiName saveNewPage()
     * @apiGroup AdminContentController
     * @apiDescription  Saving new page. Redirect to saved page preview
     * @apiParam {object} request Request object
     */
    public function saveNewPage(Request $request){
        $validator = $this->validatePage($request);

        if($validator->fails()){
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $page = new PagesSystem();
        $this->saveDataPage($request, $page);
        return redirect()->route('view_preview_page', ['id'=>$page->id]);
    }

    /** @api {public} deletePages() deletePages()
     * @apiName deletePages()
     * @apiGroup AdminContentController
     * @apiDescription  Delete page. Redirect to all pages list.
     * @apiParam {integer} id Id of page
     */
    public function deletePages($id){
        PagesSystem::where('id',$id)->delete();
        return redirect()->route('get_pages_cms');
    }

    /** @api {public} deleteAllPages() deleteAllPages()
     * @apiName deleteAllPages()
     * @apiGroup AdminContentController
     * @apiDescription  Delete all pages. Redirect to all pages list.
     */
    public function deleteAllPages(){
        PagesSystem::where('id','>',0)->delete();
        return redirect()->route('get_pages_cms');
    }

    /** @api {public} viewPreviewPage() viewPreviewPage()
     * @apiName viewPreviewPage()
     * @apiGroup AdminContentController
     * @apiParam {integer} id Id of page
     * @apiDescription  Get preview page.
     */
    public function viewPreviewPage($id){
        $page = PagesSystem::find($id);

        $this->data['page_data'] = $page;

        return view('admin.pages.cms.view_page',$this->data);
    }

    /** @api {public} editPages() editPages()
     * @apiName editPages()
     * @apiGroup AdminContentController
     * @apiParam {integer} id Id of page
     * @apiDescription  Get page for edit page.
     */
    public function editPages($id){
        $page = PagesSystem::find($id);

        if ($page->category_id>0){
            $one = array_reverse($this->getPageCategoryArray($page));
        } else {
            $one = [['groups' =>PagesCategories::where('parent_id', 0)->get(), 'use'=>0]];
        }

        $this->data['page_categories'] = $one;
        $this->data['page_data']       = $page;
        $this->data['base_seo']        = BaseSEOData::all();

        return view('admin.pages.cms.edit_page',$this->data);
    }

    /** @api {public} savePage() savePage()
     * @apiName savePage()
     * @apiGroup AdminContentController
     * @apiDescription  Saving of edited page. Redirect to saved page preview
     * @apiParam {object} request Request object
     */
    public function savePage(Request $request){
        $validator = $this->validatePageOld($request);

        if($validator->fails()){
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $page = PagesSystem::find($request['page_id']);
        $this->saveDataPage($request, $page);
        return redirect()->route('view_preview_page', ['id'=>$page->id]);
    }

    /**
     * @param $page
     * @return array
     */
    private function getPageCategoryArray($page){
        $categories = PagesCategories::all();
        $this->categories_page = [];
        $this->recursivePageCategoryArray($categories, $page->category_id);

        return $this->categories_page;
    }

    /**
     * @param $categories
     * @param $id
     */
    private function recursivePageCategoryArray($categories, $id){
        $page_category = $categories->where('id', $id)->first();
        $this->categories_page[] =  ['groups' =>$categories->where('parent_id', $page_category->parent_id),'use'=>$page_category->id];

        if($page_category->parent_id>0){
            $this->recursivePageCategoryArray($categories, $page_category->parent_id);
        }
    }

    /** @api {public} publicPage() publicPage()
     * @apiName publicPage()
     * @apiGroup AdminContentController
     * @apiDescription  Update public status of page
     * @apiParam {integer} id Pages id
     * @apiSuccessExample Success-Response:
     *  back()
     */
    public function publicPage($id){
        PagesSystem::where('id', $id)->update(['status_drop_id'=>1, 'status_drop_name'=>'Publish']);
        return back();
    }

    /** @api {public} hiddenPage() hiddenPage()
     * @apiName hiddenPage()
     * @apiGroup AdminContentController
     * @apiDescription  Update public status of page
     * @apiParam {integer} id Pages id
     * @apiSuccessExample Success-Response:
     *  back()
     */
    public function hiddenPage($id){
        PagesSystem::where('id', $id)->update(['status_drop_id'=>0, 'status_drop_name'=>'Unpublished']);
        return back();
    }

    /** @api {public} draftPage() draftPage()
     * @apiName draftPage()
     * @apiGroup AdminContentController
     * @apiDescription  Update public status of page
     * @apiParam {integer} id Pages id
     * @apiSuccessExample Success-Response:
     *  back()
     */
    public function draftPage($id){
        PagesSystem::where('id', $id)->update(['status_drop_id'=>2, 'status_drop_name'=>'Draft']);
        return back();
    }

    /** @api {public} publicPageAll() publicPageAll()
     * @apiName publicPageAll()
     * @apiGroup AdminContentController
     * @apiDescription  Update public status of all pages
     * @apiSuccessExample Success-Response:
     *  back()
     */
    public function publicPageAll(){
        PagesSystem::where('status_drop_id', 0)->update(['status_drop_id'=>1, 'status_drop_name'=>'Publish']);
        return back();
    }

    /** @api {public} hiddenPageAll() hiddenPageAll()
     * @apiName hiddenPageAll()
     * @apiGroup AdminContentController
     * @apiDescription  Update public status of all pages
     * @apiSuccessExample Success-Response:
     *  back()
     */
    public function hiddenPageAll(){
        PagesSystem::where('status_drop_id', 1)->update(['status_drop_id'=>0, 'status_drop_name'=>'Unpublished']);
        return back();
    }

    /**
     *
     */
    public function getCategoriesTable(){

    }

    /**
     * @param $products
     * @return array
     */
    private function getArreyProduct($products){
        $arr_products = [];
        $i=0;

        foreach ($products as $product){
            $arr_products[$i]['id']     = $product->id;
            $arr_products[$i]['text']   = $product->title;
            $arr_products[$i]['icon']   = '/assets/img/icon/text_1.png';
            $arr_products[$i]['a_attr'] = ['href'=>'/admin/product/'.$product->id, 'id'=>$product->id];

            $i++;
        }

        return $arr_products;
    }





























    /**
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function viewImages(){
        $this->data['images'] = ContentImage::all();

        return view('admin.pages.cms.view_image',$this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addImage(Request $request){
        if ($request->hasFile('imgs')){
            foreach ($request->file('imgs') as $file){
                $file_url = $file->store('public/cms_img');
                $file_url = substr($file_url,6,strlen($file_url)-1);
                $content_images = new ContentImage();
                $content_images->url_img = $file_url;
                $content_images->type_file = $file->getMimeType();
                $content_images->save();
            }
        }
        return back();
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteImage($id){

        $content_images = ContentImage::find($id);

        Storage::delete('public'.$content_images->url_img);

        $content_images = ContentImage::find($id)->delete();

        return back();
    }

    /**
     * @param $id
     * @return \Illuminate\Support\Collection
     */
    public function getPagesCategory($id){
        $category      = PagesCategories::all();
        $categories_id = $this->recursiveCategoriesID($category, $id);
        $pages         = PagesSystem::whereIn('category_id', $categories_id)->get();

        return $pages;
    }

    /**
     * @param $id
     * @return mixed|static
     */
    public function getBasePage($id){
        return PagesSystem::find($id);
    }

    /**
     * @param $id
     * @return mixed|static
     */
    public function getBaseSEO($id){
        return BaseSEOData::find($id);
    }

    /**
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getSEOBaseHeaders(){
        $this->data['seo_dates']  = BaseSEOData::all();

        return view('admin.pages.cms.header_seo',$this->data);
    }

    /**
     * @param Request $request
     * @return BaseSEOData
     */
    public function addSEOElement(Request $request){
        $seo = new BaseSEOData();

        $seo->value = $request['value'];
        $seo->name  = $request['name'];
        $seo->note  = $request['note'];

        $seo->save();

        return $seo;
    }

    /**
     * @param Request $request
     * @return mixed|static
     */
    public function saveSEOElement(Request $request){
        $seo = BaseSEOData::find($request['id']);

        if(isset($request['value'])&&$seo['value'] != $request['value']){
            $seo['value'] = $request['value'];
        }
        if(isset($request['name'])&&$seo['name'] != $request['name']){
            $seo['name'] = $request['name'];
        }
        if(isset($request['note'])&&$seo['note'] != $request['note']){
            $seo['note'] = $request['note'];
        }

        $seo->save();

        return $seo;
    }

    /**
     * @param $id
     * @return array
     */
    public function deleteSEOElement($id){
        BaseSEOData::where('id',$id)->delete();
        return ['status'=>1];
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteAllSEO(){
        BaseSEOData::where('id','>',0)->delete();
        return back();
    }
}
