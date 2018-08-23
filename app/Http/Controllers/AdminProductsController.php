<?php

namespace App\Http\Controllers;

use App\BaseSEOData;
use App\Category;
use App\CategoryParameter;
use App\CategoryTeg;
use App\IconsGroup;
use App\Price;
use App\Product;
use App\ProductComponentsImage;
use App\ProductComponentsText;
use App\ProductImage;
use App\ProductPreview;
use App\TagsOfProduct;
use App\User;
use App\UserGroup;
use Faker\Provider\Image;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Validator;

/**@api AdminProductsController
 * @apiName AdminProductsController
 * @apiGroup AdminProductsController
 * @apiDescription App\Http\Controllers Class AdminProductsController
 */
class AdminProductsController extends AdminBaseController
{
    private $categories_save_id = [];
    private $categories_save = [];
    private $categories = [];
    private $categories_page = [];

    /** @api {public} getCategories() getCategories()
     * @apiName getCategories()
     * @apiGroup AdminProductsController
     * @apiDescription  Return view with category of products management.
     */
    public function getCategories()
    {
        $this->data['categories'] = Category::all();
        return view('admin.pages.product.categories_prod_pages', $this->data);
    }

    /** @api {public} postCategories() postCategories()
     * @apiName postCategories()
     * @apiGroup AdminProductsController
     * @apiDescription  Return data category of products management.
     * @apiSuccess {array} categories array of categories data
     * @apiSuccessExample Success-Response:
     *  [
     *      {
     *          'id',
     *          'text',
     *      },
     *      ........
     *  ]
     */
    public function postCategories()
    {
        $categories = Category::all();
        $i = 0;
        $categories_out = [];

        foreach ($categories as $category) {
            $categories_out[$i]['id'] = $category->id;
            $categories_out[$i]['text'] = $category->name;
            $i++;
        }

        return json_encode($categories_out);
    }

    /** @api {public} saveCategories() saveCategories()
     * @apiName saveCategories()
     * @apiGroup AdminProductsController
     * @apiDescription Save new category
     * @apiParam {object} request Request object
     * @apiParam {string} request.name New category name
     * @apiSuccessExample Success-Response:
     *  back()
     */
    public function saveCategories(Request $request)
    {
        $category = new Category();

        $category->name = $request['name'];

        $category->save();

        return back();
    }

    /** @api {public} setActiveCategory() setActiveCategory()
     * @apiName setActiveCategory()
     * @apiGroup AdminProductsController
     * @apiDescription  Return data of category.
     * @apiParam {object} request Request object
     * @apiParam {int} request.id Category id
     * @apiSuccess {int} id Category id
     * @apiSuccess {string} name Category name
     * @apiSuccess {bool} status Category status
     * @apiSuccess {string} image Object of Category image
     * @apiSuccess {string} parameters_str String of Category parameters
     * @apiSuccess {array} parameters Array Objects of Category parameters
     * @apiSuccessExample Success-Response:
     *  [
     *          'id',
     *          'name',
     *          'status',
     *          'image',
     *          'parameters_str',
     *          'parameters',
     *  ]
     */
    public function setActiveCategory(Request $request)
    {
        $categories = Category::all();
        $id = $request['id'];
        $category = Category::where('id', $id)->first();
        $category_name = $category->name;

        if ($category->product_image_id > 0) {
            $img = ProductImage::find($category->product_image_id);
        } else {
            $img = false;
        }

        $parameters_str_cat = CategoryParameter::where('category_id', $id)->pluck('name')->toArray();
        $parameters = CategoryParameter::where('category_id', $id)->get();

        $parameters_str = '';
        foreach ($parameters_str_cat as $parameter){
            $parameters_str .= $parameter.',';
        }

        return json_encode(["id" => "$id", "name" => "$category_name", "status" => "$category->active", "image" => "$img", "parameters_str" => $parameters_str, "parameters" => $parameters]);
    }

    /** @api {public} getActiveCategory() getActiveCategory()
     * @apiName getActiveCategory()
     * @apiGroup AdminProductsController
     * @apiDescription  Update status of category.
     * @apiParam {object} request Request object
     * @apiParam {int} request.id Category id
     * @apiParam {int} request.status Category status
     * @apiSuccess {bool} status Category status
     * @apiSuccessExample Success-Response:
     *  ["status" => value]
     */
    public function getActiveCategory(Request $request)
    {
        Category::where('id', $request['id'])->update(['active' => $request['status']]);
        $category = Category::find($request['id']);
        return json_encode(["status" => "$category->active"]);
    }

    /** @api {public} getActiveParameter() getActiveParameter()
     * @apiName getActiveParameter()
     * @apiGroup AdminProductsController
     * @apiDescription  Update status of parameter.
     * @apiParam {object} request Request object
     * @apiParam {int} request.id Parameter id
     * @apiParam {int} request.status Parameter status
     * @apiSuccess {bool} status Parameter status
     * @apiSuccessExample Success-Response:
     *  ["status" => value]
     */
    public function getActiveParameter(Request $request)
    {
        CategoryParameter::where('id', $request['id'])->update(['status' => $request['status']]);
        $category = CategoryParameter::find($request['id']);
        return json_encode(["status" => "$category->status"]);
    }

    /** @api {public} getActiveCategoryAll() getActiveCategoryAll()
     * @apiName getActiveCategoryAll()
     * @apiGroup AdminProductsController
     * @apiDescription  Update status of all category.
     * @apiParam {object} request Request object
     * @apiParam {int} request.status Category status
     */
    public function getActiveCategoryAll(Request $request)
    {
        Category::where('id', '>', 0)->update(['active' => $request['status']]);
        $category = Category::find($request['id']);
        return json_encode(["status" => "$category->active"]);
    }

    /** @api {public} getParameterCategory() getParameterCategory()
     * @apiName getParameterCategory()
     * @apiGroup AdminProductsController
     * @apiDescription  Return the category data with tags.
     * @apiParam {int} id Category id
     */
    public function getParameterCategory($id){
        return CategoryParameter::where('id',$id)->with('tags')->first();
    }

//    /**
//     * @param $id
//     * @return $this
//     */
//    public function getTagParameterCategory($id){
//        return CategoryTeg::where('category_parameter_id',$id);
//    }

    /** @api {public} saveTagsParameter() saveTagsParameter()
     * @apiName saveTagsParameter()
     * @apiGroup AdminProductsController
     * @apiDescription  Save tags for category.
     * @apiParam {int} id Parameter id
     * @apiParam {object} request Request object
     * @apiParam {string} request.parameters String with Tags data
     * @apiSuccess {array} tags Array Objects category tags data
     */
    public function saveTagsParameter(Request $request, $id){
        $teg_old = CategoryTeg::where('category_parameter_id', $id)->get();

        $parameters = explode(',', $request['parameters']);
        $delete_teg = $teg_old->whereNotIn('name',$parameters);
        $id_del_teg = $delete_teg->pluck('id')->toArray();
        $name_teg = $teg_old->pluck('name')->toArray();
        $new_name_cat = array_diff($parameters, $name_teg);

        CategoryTeg::whereIn('id',$id_del_teg)->delete();

        $category_par = CategoryParameter::where('id',$id)->first();

        foreach ($new_name_cat as $item) {
            if($item != ''){
                $category = new CategoryTeg();
                $category->name = $item;
                $category->category_id = $category_par['category_id'];
                $category->category_parameter_id = $id;
                $category->save();
            }
        }

        return  CategoryTeg::where('category_parameter_id', $id)->get();
    }

    /** @api {public} getTags() getTags()
     * @apiName getTags()
     * @apiGroup AdminProductsController
     * @apiDescription  Return tags of category.
     * @apiParam {int} id Parameter id
     * @apiSuccess {array} tags Array Objects category tags data
     */
    public function getTags($id){
        return CategoryTeg::where('category_parameter_id',$id)->get();
    }

    /** @api {public} saveTagPrice() saveTagPrice()
     * @apiName saveTagPrice()
     * @apiGroup AdminProductsController
     * @apiDescription  Update price of tag.
     * @apiParam {int} id Tag id
     * @apiParam {object} request Request object
     * @apiParam {string} request.price Price of Tag
     * @apiSuccess {bool} status Updating status
     * @apiSuccessExample Success-Response:
     *  ["status" => 1]
     */
    public function saveTagPrice(Request $request, $id){
        CategoryTeg::where('id',$id)->update(['price' =>$request['price']]);

        return ['status' =>1];
    }

    /** @api {public} deleteCategory() deleteCategory()
     * @apiName deleteCategory()
     * @apiGroup AdminProductsController
     * @apiDescription  Delete category with parameters and tags.
     * @apiParam {int} id Category id
     * @apiSuccess {bool} status Updating status
     * @apiSuccessExample Success-Response:
     *  ["status" => 1]
     */
    public function deleteCategory($id){
         Category::where('id',$id)->delete();
         CategoryParameter::where('category_id',$id)->delete();
         CategoryTeg::where('category_id', $id)->delete();

         return ['status'=>1];
    }

    /** @api {public} getTagData() getTagData()
     * @apiName getTagData()
     * @apiGroup AdminProductsController
     * @apiDescription  return tags data.
     * @apiParam {int} id Tag id
     * @apiSuccess {object} tag Object tads data
     */
    public function getTagData($id){
        return CategoryTeg::find($id);
    }

    /** @api {public} saveNameTag() saveNameTag()
     * @apiName saveNameTag()
     * @apiGroup AdminProductsController
     * @apiDescription  Update tags data.
     * @apiParam {object} request Request object
     * @apiParam {string} request.id Id of Tag
     * @apiParam {string} request.name New name of Tag
     * @apiSuccess {object} tag Object tads data
     */
    public function saveNameTag(Request $request){
        $param = CategoryTeg::where('id',$request['id'])->first();
        $param->name = $request['name'];
        $param->save();

        $parameters_str_cat = CategoryTeg::where('category_parameter_id', $param->category_parameter_id)->pluck('name')->toArray();
        $parameters_str = '';

        foreach ($parameters_str_cat as $parameter){
            $parameters_str .= $parameter.',';
        }

        return CategoryTeg::find($request['id']);
    }

    /** @api {public} saveParameter() saveParameter()
     * @apiName saveParameter()
     * @apiGroup AdminProductsController
     * @apiDescription  Update parameters data.
     * @apiParam {object} request Request object
     * @apiParam {string} request.id Id of parameter
     * @apiParam {string} request.name New name of parameter
     * @apiSuccess {object} parameter Object parameter data
     * @apiSuccess {object} parameters_str String parameter data
     */
    public function saveParameter(Request $request){
        $param = CategoryParameter::where('id',$request['id'])->first();
        $param->name = $request['name'];
        $param->save();

        $parameters_str_cat = CategoryParameter::where('category_id', $param->category_id)->pluck('name')->toArray();
        $parameters_str = '';

        foreach ($parameters_str_cat as $parameter){
            $parameters_str .= $parameter.',';
        }

        return ['parameter' => CategoryParameter::find($request['id']), 'parameters_str' => $parameters_str];
    }

    /** @api {public} getActiveTag() getActiveTag()
     * @apiName getActiveTag()
     * @apiGroup AdminProductsController
     * @apiDescription  Update status of tag.
     * @apiParam {object} request Request object
     * @apiParam {int} request.id Tag id
     * @apiParam {int} request.status Tag status
     * @apiSuccess {bool} status Tag status
     * @apiSuccessExample Success-Response:
     *  ["status" => value]
     */
    public function getActiveTag(Request $request){
        CategoryTeg::where('id', $request['id'])->update(['status' => $request['status']]);
        $category = CategoryTeg::find($request['id']);
        return json_encode(["status" => "$category->status"]);
    }

    /** @api {public} saveParameterCategory() saveParameterCategory()
     * @apiName saveParameterCategory()
     * @apiGroup AdminProductsController
     * @apiDescription  Save parameter of category.
     * @apiParam {int} id Category id
     * @apiParam {object} request Request object
     * @apiParam {int} request.parameters Parameters data string
     * @apiSuccess {array} parameters Array objects with parameters data of category
     */
    public function saveParameterCategory(Request $request, $id){
        $category_old = CategoryParameter::where('category_id', $id)->get();

        $parameters = explode(',', $request['parameters']);
        $delete_category = $category_old->whereNotIn('name',$parameters);
        $id_del_cat = $delete_category->pluck('id')->toArray();
        $name_cat = $category_old->pluck('name')->toArray();
        $new_name_cat = array_diff($parameters, $name_cat);

        CategoryParameter::whereIn('id', $id_del_cat)->delete();
        CategoryTeg::where('category_id', $id)->whereIn('category_parameter_id',$id_del_cat)->delete();

        foreach ($new_name_cat as $item) {
            if($item != ''){
                $category = new CategoryParameter();
                $category->name = $item;
                $category->category_id = $id;
                $category->save();
            }
        }

        return  CategoryParameter::where('category_id', $id)->get();
    }

    /** @api {public} getTemplateProduct() getTemplateProduct()
     * @apiName getTemplateProduct()
     * @apiGroup AdminProductsController
     * @apiDescription  Return view with template product.
     */
    public function getTemplateProduct()
    {
        $this->data['icons'] = IconsGroup::where('id', '>', 1)->with('images')->get();
        $this->data['base_seo'] = BaseSEOData::all();
        $groups = UserGroup::where('base_group_id', '>', '3')->pluck('id')->toArray();

        $this->data['users'] = User::whereIn('group_id', $groups)->get();
        $this->data['prod_categories'] = Category::all();
        $this->data['base_products'] = Product::where('is_base', 1)->with('preview', 'category', 'previews')->get();
        return view('admin.pages.product.template_product', $this->data);
    }

    /** @api {private} saveImageBase64() saveImageBase64()
     * @apiName saveImageBase64()
     * @apiGroup AdminProductsController
     * @apiDescription  Saves the image from the base64 encoding to the file
     * @apiParam {string} img String with images data in base64
     * @apiSuccess {string} url URL saved image
     */
    private function saveImageBase64($img)
    {
        $img = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $img));
        $url = 'storage/products_img/' . md5(uniqid(rand(), true)) . '.png';
        file_put_contents($url, $img);

        $url_real = str_replace('storage', "", $url);

        return $url_real;
    }

    /** @api {private} saveProductPreview() saveProductPreview()
     * @apiName saveProductPreview()
     * @apiGroup AdminProductsController
     * @apiDescription  Saves product preview to DB
     * @apiParam {string} url URL saving image
     * @apiParam {string} title Title saving image
     * @apiParam {int} prod_id Products id saving image
     * @apiParam {bool} front_back Front status saving image
     * @apiSuccess {object} product_preview Object ProductPreview
     */
    private function saveProductPreview($url, $title, $prod_id, $front_back)
    {
        $product_prewiev = new ProductPreview();
        $product_prewiev->url = $url;
        $product_prewiev->title = 'Preview ' . $title;
        $product_prewiev->product_id = $prod_id;
        $product_prewiev->front_back = $front_back;
        $product_prewiev->save();

        return $product_prewiev;
    }

    /** @api {public} editPreviewProd() editPreviewProd()
     * @apiName editPreviewProd()
     * @apiGroup AdminProductsController
     * @apiDescription  Return view for update products preview
     * @apiParam {int} id Products id saving image
     */
    public function editPreviewProd($id)
    {
        $this->data['images'] = ProductImage::where('id', '>', 0)->with('iconsGroup')->get();
        $this->data['icons_groups'] = IconsGroup::all();
        $this->data['product_preview'] = ProductPreview::find($id);

        return view('admin.pages.product.edit_preview', $this->data);

    }

    /** @api {public} saveUpdatePreviewProd() saveUpdatePreviewProd()
     * @apiName saveUpdatePreviewProd()
     * @apiGroup AdminProductsController
     * @apiDescription Save update preview of product
     * @apiParam {int} id Product preview id
     * @apiParam {object} request Request object
     * @apiParam {file} request.img Image preview file
     * @apiSuccessExample Success-Response:
     *  ['status'=>'1']
     */
    public function saveUpdatePreviewProd(Request $request, $id)
    {
        $preview = ProductPreview::find($id);

        Storage::delete('public' . $preview['url']);

        $file_url = $request->file('img')->store('public/products_img');
        $file_url = substr($file_url, 6, strlen($file_url) - 1);

        ProductPreview::where('id', $id)->update(['url' => $file_url]);

        return ['status' => 1];
    }

    /** @api {public} saveTemplateProduct() saveTemplateProduct()
     * @apiName saveTemplateProduct()
     * @apiGroup AdminProductsController
     * @apiDescription Save template of product
     * @apiParam {object} request Request object with product data
     * @apiSuccess {object} product saved Object Product
     */
    public function saveTemplateProduct(Request $request)
    {
        $product = new Product();

        $product->category_id = $request['category'];
        $product->status_active = 0;
        $product->status_approved = 0;
        $product->is_base = 1;
        $product->preview_image_id = 0;
        $product->name = $request['template_name'];
        $product->url = md5(uniqid(rand(), true));
        $product->user_id = 0;

        $product->save();

        return $this->saveTemplateProd($request, $product);
    }

    /** @api {private} deleteAllImgProd() deleteAllImgProd()
     * @apiName deleteAllImgProd()
     * @apiGroup AdminProductsController
     * @apiDescription  delete all image of product
     * @apiParam {int} id Products id
     */
    private function deleteAllImgProd($id)
    {
        $imgs = ProductComponentsImage::where('product_id', $id)->get();
        $previews = ProductPreview::where('product_id', $id)->get();

        $urls = [];

        foreach ($previews as $preview) {
            $urls[] = 'public' . $preview->url;
        }

        foreach ($imgs as $img) {
            $urls[] = 'public' . $img->url;
        }

        ProductComponentsImage::where('product_id', $id)->delete();
        ProductComponentsText::where('product_id', $id)->delete();
        ProductPreview::where('product_id', $id)->delete();
    }

    /** @api {private} saveTitleImgProd() saveTitleImgProd()
     * @apiName saveTitleImgProd()
     * @apiGroup AdminProductsController
     * @apiDescription  Save title image of product
     * @apiParam {object} request Object Request
     * @apiParam {object} product Object Product
     * @apiParam {string} title Products title
     * @apiSuccess {object} product saved Object Product
     */
    private function saveTitleImgProd(Request $request, Product $product, $title)
    {
        $url_real = $this->saveImageBase64($request['product_pr']);
        $product_prewiev_front = $this->saveProductPreview($url_real, $title, $product->id, 0);

        $product->preview_image_id = $product_prewiev_front->id;
        $product->save();

        if (count($request['product']) > 1) {
            $url_real = $this->saveImageBase64($request['product_pr']);
            $product_prewiev_back = $this->saveProductPreview($url_real, $title, $product->id, 1);
        }

        return $product;
    }

    /** @api {private} saveImagesProd() saveImagesProd()
     * @apiName saveImagesProd()
     * @apiGroup AdminProductsController
     * @apiDescription  Save components of product
     * @apiParam {object} request Object Request
     * @apiParam {object} product Object Product
     * @apiSuccess {array} urls_img Array images
     */
    private function saveImagesProd(Request $request, Product $product)
    {
        $i = 0;

        $urls_img = [];

        foreach ($request['product'] as $products) {
            if (isset($products['elements'])) {
                foreach ($products['elements'] as $element) {
                    if ($element['type'] == 'image') {
                        $img = $element['source'];

                        if (stripos($img, 'base64')) {

                            $url_real = $this->saveImageBase64($img);
                            $content_images = new ProductComponentsImage();
                            $content_images->url = $url_real;
                            $content_images->title = '';
                        } else {
                            $url = str_replace('/storage', "", $img);
                            $img = ProductImage::where('url', 'like', $url)->first();
                            if (count($img)) {
                                $content_images = new ProductComponentsImage();
                                $content_images->url = $img['url'];
                                $content_images->title = isset($img['title'])?$img['title']:'img';
                            } else {
                                $content_images = ProductComponentsImage::where('url', 'like', $url)->first();
                            }
                        }

                        $parametesr_img = $element['parameters'];

                        $colors = $parametesr_img['originParams']['colors'];
                        $colors_str = '';

                        foreach ($colors as $color) {
                            $colors_str .= $color . ', ';
                        }
                        $colors_str = substr($colors_str, 0, strlen($colors_str) - 2);
                        unset( $parametesr_img['originParams']['source'] );
                        $arr = [
                            'left' =>$parametesr_img['left'],
                            'z' =>$parametesr_img['originParams']['z'],
                            'rotatable' =>$parametesr_img['originParams']['rotatable'],
                            'colors' =>$parametesr_img['originParams']['colors'],
                            'removable' =>$parametesr_img['originParams']['removable'],
                            'draggable' =>$parametesr_img['originParams']['draggable'],
                            'resizable' =>$parametesr_img['originParams']['resizable'],
                            'copyable' =>$parametesr_img['originParams']['copyable'],
                            'zChangeable' =>$parametesr_img['originParams']['zChangeable'],
                            'scaleX' =>$parametesr_img['scaleX'],
                            'scaleY' =>$parametesr_img['scaleY'],
                            'top' =>$parametesr_img['top'],
                            'angle' =>intval($parametesr_img['angle']),
                            'autoSelect' =>$parametesr_img['autoSelect']=='true'?true:false,
                            'boundingBox' =>$parametesr_img['boundingBox'],
                            'boundingBoxMode' =>$parametesr_img['boundingBoxMode'],
                            'colorLinkGroup' =>$parametesr_img['colorLinkGroup']=='true'?true:false,
                            'cornerSize' =>intval($parametesr_img['cornerSize']),
                            'evented' =>$parametesr_img['originParams']['evented']=='true'?true:false,
                            'excludeFromExport' =>$parametesr_img['originParams']['excludeFromExport']=='true'?true:false,
                            'filter' =>$parametesr_img['filter']=='true'?true:false,
                            'flipX' =>$parametesr_img['flipX']=='true'?true:false,
                            'flipY' =>$parametesr_img['flipY']=='true'?true:false,
                            'height' =>intval($parametesr_img['height']),
                            'isCustom' =>$parametesr_img['isCustom']=='true'?true:false,
                            'isEditable' =>$parametesr_img['isEditable']=='true'?true:false,
                            'opacity' =>floatval($parametesr_img['opacity']),
                            'lockUniScaling' =>$parametesr_img['lockUniScaling']=='true'?true:false,
                            'originX' =>$parametesr_img['originX'],
                            'originY' =>$parametesr_img['originY'],
                            'padding' =>intval($parametesr_img['padding']),
                            'replaceInAllViews' =>$parametesr_img['replaceInAllViews']=='true'?true:false,
                            'resizeToH' =>floatval($parametesr_img['resizeToH']),
                            'resizeToW' =>floatval($parametesr_img['resizeToW']),
                            'cornerColor' =>$parametesr_img['originParams']['cornerColor'],
                            'cornerIconColor' =>$parametesr_img['originParams']['cornerIconColor'],
                            'scaleMode' =>$parametesr_img['scaleMode'],
                            'topped' =>$parametesr_img['topped']=='true'?true:false,
                            'uniScalingUnlockable' =>$parametesr_img['uniScalingUnlockable']=='true'?true:false,
                            'uploadZone' =>$parametesr_img['uploadZone']=='true'?true:false,
                        ];

                        if($parametesr_img['fill']!='false'){
                            $arr['fill'] = $parametesr_img['fill'];
                        }

                        $content_images->parameters = json_encode($arr);
                        $content_images->price = 0;
                        $content_images->front_back = $i;
                        $content_images->product_id = $product->id;

                        $content_images->save();

                        $urls_img[] = $content_images->id;
                    } else {
                        $content_text = new ProductComponentsText();
                        $parametesr_img = $element['parameters'];
                        unset( $parametesr_img['originParams']['source'] );
                        $arr = [
                            'angle' =>$parametesr_img['angle'],
                            'autoCenter' =>$parametesr_img['autoCenter']=='true'?true:false,
                            'autoSelect' =>$parametesr_img['autoSelect']=='true'?true:false,
                            'boundingBox' =>$parametesr_img['boundingBox'],
                            'boundingBoxMode' =>$parametesr_img['boundingBoxMode'],
                            'charSpacing' =>intval($parametesr_img['charSpacing']),
                            'colorLinkGroup' =>$parametesr_img['colorLinkGroup']=='true'?true:false,
                            'colors' =>$parametesr_img['colors']=='true'?true:false,
                            'copyable' =>$parametesr_img['copyable']=='true'?true:false,
                            'cornerSize' =>intval($parametesr_img['cornerSize']),
                            'curvable' =>$parametesr_img['curvable']=='true'?true:false,
                            'curveRadius' =>intval($parametesr_img['curveRadius']),
                            'curveSpacing' =>intval($parametesr_img['curveSpacing']),
                            'curveReverse' =>$parametesr_img['curveReverse']=='true'?true:false,
                            'curved' =>$parametesr_img['curved']=='true'?true:false,
                            'draggable' =>$parametesr_img['draggable']=='true'?true:false,
                            'editable' =>$parametesr_img['editable']=='true'?true:false,
                            'evented' =>$parametesr_img['evented']=='true'?true:false,
                            'excludeFromExport' =>$parametesr_img['excludeFromExport']=='true'?true:false,
                            'fill' =>$parametesr_img['fill'],
                            'flipX' =>$parametesr_img['flipX']=='true'?true:false,
                            'flipY' =>$parametesr_img['flipY']=='true'?true:false,
                            'fontFamily' =>$parametesr_img['fontFamily'],
                            'fontSize' =>intval($parametesr_img['originParams']['fontSize']),
                            'fontStyle' =>$parametesr_img['fontStyle'],
                            'fontWeight' =>$parametesr_img['fontWeight'],
                            'height' =>floatval($parametesr_img['height']),//?????['originParams']['height']
                            'isCustom' =>$parametesr_img['isCustom']=='true'?true:false,
                            'isEditable' =>$parametesr_img['isEditable']=='true'?true:false,
                            'left' =>intval($parametesr_img['left']),
                            'letterSpacing' =>intval($parametesr_img['letterSpacing']),
                            'padding' =>intval($parametesr_img['padding']),
                            'lineHeight' =>floatval($parametesr_img['lineHeight']),
                            'removable' =>$parametesr_img['removable']=='true'?true:false,
                            'rotatable' =>$parametesr_img['rotatable']=='true'?true:false,
                            'resizable' =>$parametesr_img['resizable']=='true'?true:false,
                            'stroke' =>$parametesr_img['stroke'],
                            'strokeWidth' =>intval($parametesr_img['strokeWidth']),
                            'textAlign' =>$parametesr_img['textAlign'],
                            'textBox' =>$parametesr_img['textBox']=='true'?true:false,
                            'textDecoration' =>$parametesr_img['textDecoration'],
                            'textPlaceholder' =>$parametesr_img['textPlaceholder']=='true'?true:false,
                            'top' =>intval($parametesr_img['top']),
                            'zChangeable' =>$parametesr_img['zChangeable']=='true'?true:false,
                            'topped' =>$parametesr_img['topped']=='true'?true:false,
                            'z' =>intval($parametesr_img['originParams']['z']),
                            'width' =>floatval($parametesr_img['width']),//?????['originParams']['width']
                            'type' =>$parametesr_img['originParams']['type'],
                            'scaleX' =>floatval($parametesr_img['originParams']['scaleX']),
                            'scaleY' =>floatval($parametesr_img['originParams']['scaleY']),
                        ];

                        if(isset($parametesr_img['title'])){
                            $arr['title'] = $parametesr_img['title'];
                        }

                        if(isset($parametesr_img['originParams']['radius'])){
                            $arr['radius'] = intval($parametesr_img['originParams']['radius']);
                        }

                        if(isset($parametesr_img['originParams']['spacing'])){
                            $arr['spacing'] = intval($parametesr_img['originParams']['spacing']);
                        }

                        if(isset($parametesr_img['originParams']['reverse'])){
                            $arr['reverse'] = $parametesr_img['originParams']['reverse']=='true'?true:false;
                        }

                        if(isset($parametesr_img['originParams']['effect'])){
                            $arr['effect'] = $parametesr_img['originParams']['effect'];
                        }

                        if(isset($parametesr_img['originParams']['range'])){
                            $arr['range'] = intval($parametesr_img['originParams']['range']);
                        }

                        if(isset($parametesr_img['originParams']['smallFont'])){
                            $arr['smallFont'] = intval($parametesr_img['originParams']['smallFont']);
                        }

                        if(isset($parametesr_img['originParams']['largeFont'])){
                            $arr['largeFont'] = intval($parametesr_img['originParams']['largeFont']);
                        }

                        if(isset($parametesr_img['originParams']['isEditable'])){
                            $arr['isEditable'] = $parametesr_img['originParams']['isEditable'];
                        }

                        $content_text->parameters = json_encode($arr);
                        $content_text->front_back = $i;
                        $content_text->content = $element['source'];
                        $content_text->title = isset($element['title'])?$element['title']:'text';
                        $content_text->price = 0;
                        $content_text->product_id = $product->id;

                        $content_text->save();
                    }
                }
            }

            $i++;
        }

        return $urls_img;
    }

    /** @api {private} saveTemplateProd() saveTemplateProd()
     * @apiName saveTemplateProd()
     * @apiGroup AdminProductsController
     * @apiDescription  Save template of product
     * @apiParam {object} request Object Request
     * @apiParam {object} product Object Product
     * @apiSuccessExample Success-Response:
     *  ['status'=>'1']
     */
    private function saveTemplateProd(Request $request, Product $product)
    {
        $this->saveTitleImgProd($request, $product, $request['template_name']);
        $this->saveImagesProd($request, $product);

        return ['status' => 1];
    }

    /** @api {public} saveTemplateEditProduct() saveTemplateEditProduct()
     * @apiName saveTemplateEditProduct()
     * @apiGroup AdminProductsController
     * @apiDescription  Save editing template of product
     * @apiParam {object} request Object Request
     * @apiParam {int} id Template id
     * @apiSuccessExample Success-Response:
     *  ['status'=>'1']
     */
    public function saveTemplateEditProduct(Request $request, $id)
    {
        $product = Product::where('id', $id)->first();

        $product->category_id = $request['category'];
        $product->name = $request['template_name'];

        ProductPreview::where('product_id', $id)->delete();
        ProductComponentsText::where('product_id', $id)->delete();

        $product = $this->saveTitleImgProd($request, $product, $request['template_name']);

        $urls_img = $this->saveImagesProd($request, $product);

        ProductComponentsImage::whereNotIn('id', $urls_img)->where('product_id', $id)->delete();

        return ['status' => 1];
    }

    /** @api {public} viewImages() viewImages()
     * @apiName viewImages()
     * @apiGroup AdminProductsController
     * @apiDescription  Return view with images.
     */
    public function viewImages()
    {
        $this->data['images'] = ProductImage::where('id', '>', 0)->with('iconsGroup')->get();
        $this->data['icons_groups'] = IconsGroup::all();

        return view('admin.pages.product.view_image', $this->data);
    }

    /** @api {public} addImage() addImage()
     * @apiName addImage()
     * @apiGroup AdminProductsController
     * @apiDescription  Save images
     * @apiParam {object} request Object Request
     * @apiParam {files} request.imgs Array saving images
     * @apiSuccessExample Success-Response:
     *  back()
     */
    public function addImage(Request $request)
    {
        if ($request->hasFile('imgs')) {
            foreach ($request->file('imgs') as $file) {
                $this->saveImage($file, 0, 0);
            }
        }
        return back();
    }

    /** @api {private} saveImage() saveImage()
     * @apiName saveImage()
     * @apiGroup AdminProductsController
     * @apiDescription  Save images
     * @apiParam {file} file Saved file
     * @apiParam {int} type Type id of image
     * @apiParam {int} group_icons_id Id group icons
     * @apiSuccess {object} product saved Object ProductImage
     */
    private function saveImage($file, $type, $group_icons_id)
    {
        $file_url = $file->store('public/products_img');
        $file_url = substr($file_url, 6, strlen($file_url) - 1);

        $content_images = new ProductImage();

        $content_images->url = $file_url;
        $content_images->title = '';
        $content_images->type_file = $file->getMimeType();
        $content_images->type = $type;
        $content_images->icons_group_id = $group_icons_id;

        $content_images->save();

        return $content_images;
    }

    /** @api {public} getEditImageCategory() getEditImageCategory()
     * @apiName getEditImageCategory()
     * @apiGroup AdminProductsController
     * @apiDescription  Get image category
     * @apiParam {int} id Id products image
     * @apiSuccess {object} product saved Object ProductImage
     */
    public function getEditImageCategory($id)
    {
        return ProductImage::where('id', $id)->first();
    }

    /** @api {public} saveDataImage() saveDataImage()
     * @apiName saveDataImage()
     * @apiGroup AdminProductsController
     * @apiDescription  Save image data
     * @apiParam {int} id Id products image
     * @apiParam {object} request Object Request
     * @apiParam {string} request.title Image title
     * @apiParam {int} request.group Icons Group id
     * @apiParam {int} request.left Left position
     * @apiParam {int} request.top Top position
     * @apiParam {int} request.price Price added image
     * @apiParam {bool} request.z_changeable z_changeable
     * @apiParam {bool} request.removable removable
     * @apiParam {bool} request.draggable draggable
     * @apiParam {bool} request.rotatable rotatable
     * @apiParam {bool} request.resizable resizable
     * @apiParam {dubl} request.size Size image
     * @apiSuccess {object} product saved Object ProductImage
     */
    public function saveDataImage(Request $request, $id)
    {

        $colors = $request['colors'];
        $colors_str = '';

        foreach ($colors as $color) {
            $colors_str .= $color . ', ';
        }
        $colors_str = substr($colors_str, 0, strlen($colors_str) - 2);

        $image = ProductImage::find($id);

        $image->title = $request['title'];
        $image->icons_group_id = $request['group'] > 0 ? $request['group'] : 0;
        $image->left = $request['left'] > 0 ? $request['left'] : 600;
        $image->top = $request['top'] > 0 ? $request['top'] : 300;
        $image->colors = $colors_str;
        $image->price = $request['price'] > 0 ? $request['price'] : 0;
        $image->z_changeable = $request['z_changeable'];
        $image->removable = $request['removable'];
        $image->draggable = $request['draggable'];
        $image->rotatable = $request['rotatable'];
        $image->resizable = $request['resizable'];
        $image->size = $request['size'] > 0 ? $request['size'] : 0;

        $image->save();

        return ProductImage::where('id', $id)->with('iconsGroup')->first();
    }

    /** @api {public} deleteImage() deleteImage()
     * @apiName deleteImage()
     * @apiGroup AdminProductsController
     * @apiDescription  Delete product image
     * @apiParam {int} id Product image id
     * @apiSuccessExample Success-Response:
     *  back()
     */
    public function deleteImage($id)
    {
        $content_images = ProductImage::find($id);
        Storage::delete('public' . $content_images->url);

        ProductImage::find($id)->delete();

        return back();
    }

    /** @api {public} deleteImage() deleteImage()
     *  @apiName deleteImage()
     *  @apiGroup AdminProductsController
     *  @apiDescription  Delete category image
     *  @apiParam {int} id Category image id
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function deleteImageCategory($id)
    {
        $content_images = ProductImage::find($id);

        Storage::delete('public' . $content_images->url);

        ProductImage::find($id)->delete();

        return back();
    }

    /** @api {public} addImageCategory() addImageCategory()
     *  @apiName addImageCategory()
     *  @apiGroup AdminProductsController
     *  @apiDescription  Add image of category
     *  @apiParam {object} request Object Request with file
     *  @apiSuccessExample Success-Response:
     *  ['url' => value]
     */
    public function addImageCategory(Request $request)
    {
        $img = collect();
        $img->id = 0;
        if ($request->hasFile('img')) {
            $img = $this->saveImage($request->file('img'), 1, 0);
        }

        $car = Category::where('id', $request['category_id'])->first();

        $content_images = ProductImage::find($car->product_image_id);

        if ($content_images) {
            Storage::delete($content_images->url);
            $content_images->delete();
        }

        $car->product_image_id = $img->id;
        $car->save();

        return ['url' => $img->url];
    }

    /** @api {public} addGroupIcon() addGroupIcon()
     *  @apiName addGroupIcon()
     *  @apiGroup AdminProductsController
     *  @apiDescription  Add new group of icons
     *  @apiParam {object} request Object Request with file
     *  @apiSuccess {object} group Object new IconsGroup
     */
    public function addGroupIcon(Request $request)
    {

        $group_valid = IconsGroup::where('name', $request['name'])->count();

        if ($group_valid > 0) {
            return ['status' => 1];
        } else {
            $group = new IconsGroup();
            $group->name = $request['name'];
            $group->save();

            return $group;
        }
    }

    /** @api {public} saveGroupIcon() saveGroupIcon()
     *  @apiName saveGroupIcon()
     *  @apiGroup AdminProductsController
     *  @apiDescription Update data group of icons
     *  @apiParam {object} request Object Request with file
     *  @apiParam {int} id Id of icons group
     *  @apiSuccess {object} group Object new IconsGroup
     */
    public function saveGroupIcon(Request $request, $id)
    {

        $group_valid = IconsGroup::where('name', $request['name'])->get();
        $group_valid_id = $group_valid->first();
        if ($group_valid->count() > 1 || (isset($group_valid_id['id']) && $group_valid_id['id'] != $id)) {
            return ['status' => 1];
        } else {
            $group = IconsGroup::find($id);
            $group->name = $request['name'];
            $group->save();

            return $group;
        }

    }

    /** @api {public} deleteGroupIcon() deleteGroupIcon()
     * @apiName deleteGroupIcon()
     * @apiGroup AdminProductsController
     * @apiDescription Delete Icons group with images
     * @apiParam {int} id Id of icons group
     * @apiSuccessExample Success-Response:
     *  ['status'=>'1']
     */
    public function deleteGroupIcon($id)
    {
        ProductImage::where('icons_group_id', $id)->update(['icons_group_id' => 0]);

        IconsGroup::where('id', $id)->delete();

        return ['status' => 1];
    }

    /** @api {public} getAddNewProduct() getAddNewProduct()
     * @apiName getAddNewProduct()
     * @apiGroup AdminProductsController
     * @apiDescription  Return view for added product.
     */
    public function getAddNewProduct()
    {
        $this->data['icons'] = IconsGroup::where('id', '>', 0)->with('images')->get();
        $this->data['base_seo'] = BaseSEOData::all();
        $groups = UserGroup::where('base_group_id', '>', '3')->pluck('id')->toArray();

        $this->data['users'] = User::whereIn('group_id', $groups)->get();
        $this->data['prod_categories'] = Category::all();
        $this->data['templates'] = Product::where('is_base', 1)->with('imgComponents', 'textComponents', 'category', 'previews')->get();

        return view('admin.pages.product.add_product', $this->data);
    }

    /** @api {public} getImagesCategory() getImagesCategory()
     *  @apiName getImagesCategory()
     *  @apiGroup AdminProductsController
     *  @apiDescription Get image category with images
     *  @apiParam {int} id Id of icons group
     *  @apiSuccess {object} group Object new IconsGroup
     */
    public function getImagesCategory($id)
    {
        $group = IconsGroup::where('id', $id)->with('images')->first();

        return $group;
    }

    /**
     * @param $parent_id
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|static[]
     */
    public function getSubProdCategories($parent_id)
    {
        return CategoryParameter::where('category_id', $parent_id)->with('tags')->get();
    }

//    /**
//     * @param Request $request
//     * @return mixed
//     */
//    private function validateProd(Request $request)
//    {
//        $validate = [
//            'name' => 'required|max:255|min:3',
//            'short_description' => 'required|max:255|min:3',
//            'detail_description' => 'required|min:3',
//            'min_quantity' => 'min:0',
//            'unit_step' => 'min:0',
//            'unit_price' => 'min:0'
//        ];
//
//        return Validator::make($request->all(), $validate);
//    }

    /** @api {public} saveNewProduct() saveNewProduct()
     *  @apiName saveNewProduct()
     *  @apiGroup AdminProductsController
     *  @apiDescription save new product
     *  @apiParam {object} request Object Request with product data
     *  @apiSuccessExample Success-Response:
     *  ['status'=>'1']
     */
    public function saveNewProduct(Request $request)
    {
        $newProduct = new Product();

        $category = isset($request['group_id']) ? $request['group_id'] : 0;
        $newProduct->category_id = $category;
        $newProduct->status_active = isset($request['status_prop']) ? $request['status_prop'] : 0;
        $newProduct->status_approved = 0;
        $newProduct->preview_image_id = 0;
        $newProduct->pricing_to_step = isset($request['pricing']) ? $request['pricing'] : 0;
        $newProduct->name = $request['name_prod'];
        $newProduct->url = $request['url_prod_h'];
        $newProduct->short_description = $request['short_description'];
        $newProduct->product_description = $request['detail_description'];
        $newProduct->seo_data = isset($request['SEO_other_data']) ? $request['SEO_other_data'] : '';
        $newProduct->description = isset($request['seo_description']) ? $request['seo_description'] : '';
        $newProduct->min_quantity = isset($request['min_quantity']) && !empty($request['min_quantity']) ? $request['min_quantity'] : 0;
        $newProduct->unit_step = isset($request['unit_step']) && !empty($request['unit_step']) ? $request['unit_step'] : 0;
        $newProduct->price_one_step = isset($request['unit_price']) && !empty($request['unit_price']) ? $request['unit_price'] : 0;
        $newProduct->user_id = isset($request['author']) && $request['author'] !=0?$request['author']:Auth::id();
        $newProduct->discount = isset($request['discount'])  ? $request['discount'] : 0;
        $newProduct->featured = isset($request['featured']);
        $newProduct->is_base = 0;

        $newProduct->save();

        $newProduct->url = '/product/' . $request['url_prod_h'] . $newProduct->id;

        $newProduct->save();

        if ($request->has('tags')&&count($request['tags'])>0){
            $category_tags = CategoryTeg::whereIn('name',$request['tags'])->get();

            foreach ($request['tags'] as $tag){
                $category_tag = $category_tags->where('name',$tag)->first();

                $tag_save = new TagsOfProduct();
                $tag_save->product_id = $newProduct->id;
                $tag_save->category_teg_id = $category_tag->id;
                $tag_save->save();
            }
        }

        if ($request['pricing'] == 1 && $request->has('prices')) {
            $prices = $request['prices'];

            for ($i = 0; $i < count($prices); $i++) {
                $quantity_price = new Price();

                $quantity_price->product_id = $newProduct->id;
                $quantity_price->value = $prices[$i]['price'];
                $quantity_price->quantity = $prices[$i]['count'];

                $quantity_price->save();
            }
        }

        $newProduct = $this->saveTitleImgProd($request, $newProduct, $request['name_prod']);
        $this->saveImagesProd($request, $newProduct);

        return ['status' => 1];
    }

    /** @api {public} saveEditProduct() saveEditProduct()
     *  @apiName saveEditProduct()
     *  @apiGroup AdminProductsController
     *  @apiDescription save edit product
     *  @apiParam {object} request Object Request with product data
     *  @apiParam {int} id Id of product
     *  @apiSuccessExample Success-Response:
     *  ['status'=>'1']
     */
    public function saveEditProduct(Request $request, $id)
    {
        $product = Product::where('id', $id)->first();

        $category = isset($request['group_id']) ? $request['group_id'] : 0;
        $product->category_id = $category;
        $product->status_active = isset($request['status_prop']) ? $request['status_prop'] : 0;
        $product->pricing_to_step = isset($request['pricing']) ? $request['pricing'] : 0;
        $product->name = $request['name_prod'];
        $product->url = $request['url_prod_h'];
        $product->short_description = $request['short_description'];
        $product->product_description = $request['detail_description'];
        $product->seo_data = isset($request['SEO_other_data']) ? $request['SEO_other_data'] : '';
        $product->description = isset($request['seo_description']) ? $request['seo_description'] : '';
        $product->min_quantity = isset($request['min_quantity']) && !empty($request['min_quantity']) ? $request['min_quantity'] : 0;
        $product->unit_step = isset($request['unit_step']) && !empty($request['unit_step']) ? $request['unit_step'] : 0;
        $product->price_one_step = isset($request['unit_price']) && !empty($request['unit_price']) ? $request['unit_price'] : 0;
        $product->user_id = isset($request['author']) && $request['author'] !=0?$request['author']:Auth::id();
        $product->discount = isset($request['discount']) ? $request['discount'] : 0;
        $product->featured = isset($request['featured']);
        $product->is_base = 0;

        $product->save();

        if ($request['pricing'] == 1 && $request->has('prices')) {
            Price::where('product_id', $product->id)->delete();

            $prices = $request['prices'];

            for ($i = 0; $i < count($prices); $i++) {
                $quantity_price = new Price();

                $quantity_price->product_id = $product->id;
                $quantity_price->value = $prices[$i]['price'];
                $quantity_price->quantity = $prices[$i]['count'];

                $quantity_price->save();
            }
        }

        ProductPreview::where('product_id', $id)->delete();
        ProductComponentsText::where('product_id', $id)->delete();

        $product = $this->saveTitleImgProd($request, $product, $request['template_name']);

        $urls_img = $this->saveImagesProd($request, $product);

        ProductComponentsImage::whereNotIn('id', $urls_img)->where('product_id', $id)->delete();

        return ['status' => 1];
    }

    /** @api {public} getProductsAll() getProductsAll()
     * @apiName getProductsAll()
     * @apiGroup AdminProductsController
     * @apiDescription  Return view with all product.
     */
    public function getProductsAll()
    {
        $this->data['products'] = Product::where('id', '>', 0)->where('is_base', 0)->with('user', 'previews')->get();
        $this->data['categories'] = collect($this->recursiveCategoriesText(Category::all(), Category::all()));
        return view('admin.pages.product.all_product', $this->data);
    }

    /** @api {public} getProductsGroup() getProductsGroup()
     * @apiName getProductsGroup()
     * @apiGroup AdminProductsController
     * @apiParam {int} id Id of products group
     * @apiDescription  Return view with all product of group.
     */
    public function getProductsGroup($id)
    {
        $categories_id = $this->recursiveCategoriesID(Category::all(), $id);

        $this->data['products'] = Product::whereIn('category_id', $categories_id)->where('is_base', 0)->with('user')->get();
        $this->data['categories'] = Category::all();

        return view('admin.pages.product.all_product', $this->data);
    }

//    /**
//     * @param $parents
//     * @param $categories
//     * @return array
//     */
//    private function recursiveCategoriesText($parents, $categories)
//    {
//        $categories_out = [];
//        $i = 0;
//
//        foreach ($parents as $parent) {
//            $categories_out[$i]['id'] = $parent->id;
//            $categories_out[$i]['name'] = $parent->name;
//            $children = $categories->where('parent_id', $parent->id);
//
//            if ($children->count() > 0) {
//                $childrens = $this->recursiveCategoriesText($children, $categories);
//
//                foreach ($childrens as $key => $children) {
//                    $childrens[$key]['name'] = $parent->name . '/' . $children['name'];
//                }
//
//                $categories_out = array_merge($categories_out, $childrens);
//                $i = count($categories_out);
//            }
//            $i++;
//        }
//
//        return $categories_out;
//    }
//
//    /**
//     * @param $category
//     * @param $categori_id
//     * @return array
//     */
//    private function recursiveCategoriesID($category, $categori_id)
//    {
//        $first = $category->where('id', $categori_id)->first();
//        $categories_out = [$first->id];
//        $chields = $category->where('parent_id', $categori_id);
//
//        foreach ($chields as $chield) {
//            $categories_out[] = $chield->id;
//            $children = $category->where('parent_id', $chield->id);
//
//            if ($children->count() > 0) {
//                $categories_out = array_merge($categories_out, $this->recursiveCategoriesID($category, $chield->id));
//            }
//        }
//
//        return array_unique($categories_out);
//    }

    /** @api {private} deleteAllImgAllProd() deleteAllImgAllProd()
     * @apiName deleteAllImgAllProd()
     * @apiGroup AdminProductsController
     * @apiParam {int} id Id of product
     * @apiDescription  Delete all image of product.
     */
    private function deleteAllImgAllProd($is_base)
    {
        $prods_id = Product::where('is_base', $is_base)->pluck('id')->toArray();
        $previews = ProductPreview::whereIn('product_id', $prods_id)->get();
        $imgs = ProductComponentsImage::whereIn('product_id', $prods_id)->get();

        $urls = [];

        foreach ($previews as $preview) {
            $urls[] = 'public' . $preview->url;
        }

        foreach ($imgs as $img) {
            $urls[] = 'public' . $img->url;
        }

        Product::where('is_base', $is_base)->delete();
        ProductPreview::whereIn('product_id', $prods_id)->delete();
        ProductComponentsImage::whereIn('product_id', $prods_id)->delete();
        ProductComponentsText::whereIn('product_id', $prods_id)->delete();
    }

    /** @api {public} deleteAllProd() deleteAllProd()
     *  @apiName deleteAllProd()
     *  @apiGroup AdminProductsController
     *  @apiDescription Delete all product
     *  @apiSuccessExample Success-Response:
     *  ['status'=>'1']
     */
    public function deleteAllProd()
    {
        $this->deleteAllImgAllProd(0);
        return ['status' => 1];
    }

    /** @api {public} deleteProdId() deleteProdId()
     *  @apiName deleteProdId()
     *  @apiGroup AdminProductsController
     *  @apiParam {int} id Id of product with image
     *  @apiDescription Delete product from id
     *  @apiSuccessExample Success-Response:
     *  ['status'=>'1']
     */
    public function deleteProdId($id)
    {
        Product::where('id', $id)->where('is_base', 0)->delete();

        $this->deleteAllImgProd($id);

        return ['status' => 1];
    }

    /** @api {public} deleteAllProdCategory() deleteAllProdCategory()
     *  @apiName deleteAllProdCategory()
     *  @apiGroup AdminProductsController
     *  @apiParam {int} id Id of product category
     *  @apiDescription Delete products of category
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function deleteAllProdCategory($id)
    {
        Product::where('category_id', $id)->delete();

        return back();
    }

    /** @api {public} inactiveAllProd() inactiveAllProd()
     *  @apiName inactiveAllProd()
     *  @apiGroup AdminProductsController
     *  @apiDescription Update active status of all product
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function inactiveAllProd()
    {
        Product::where('id', '>', 0)->update(['status_active' => 0]);

        return back();
    }

    /** @api {public} inactiveProdId() inactiveProdId()
     *  @apiName inactiveProdId()
     *  @apiGroup AdminProductsController
     *  @apiParam {int} id Id of product
     *  @apiDescription Update active status of product
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function inactiveProdId($id)
    {
        Product::where('id', $id)->update(['status_active' => 0]);

        return ['status' => 1];
    }

    /** @api {public} inactiveAllProdCategory() inactiveAllProdCategory()
     *  @apiName inactiveAllProdCategory()
     *  @apiGroup AdminProductsController
     *  @apiParam {int} id Id of products category
     *  @apiDescription Update active status of products of category
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function inactiveAllProdCategory($id)
    {
        $categories_id = $this->recursiveCategoriesID(Category::all(), $id);

        Product::whereIn('category_id', $categories_id)->update(['status_active' => 0]);

        return back();
    }

    /** @api {public} activeAllProd() activeAllProd()
     *  @apiName activeAllProd()
     *  @apiGroup AdminProductsController
     *  @apiDescription Update active status of all product
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function activeAllProd()
    {
        Product::where('id', '>', 0)->update(['status_active' => 1]);

        return back();
    }

    /** @api {public} activeProdId() activeProdId()
     *  @apiName activeProdId()
     *  @apiGroup AdminProductsController
     *  @apiParam {int} id Id of product
     *  @apiDescription Update active status of product
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function activeProdId($id)
    {
        Product::where('id', $id)->update(['status_active' => 1]);

        return ['status' => 1];
    }

    /** @api {public} activeAllProdCategory() activeAllProdCategory()
     *  @apiName activeAllProdCategory()
     *  @apiGroup AdminProductsController
     *  @apiParam {int} id Id of products category
     *  @apiDescription Update active status of products of category
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function activeAllProdCategory($id)
    {
        $categories_id = $this->recursiveCategoriesID(Category::all(), $id);

        Product::whereIn('category_id', $categories_id)->update(['status_active' => 1]);

        return back();
    }

    /** @api {public} hideAllProd() hideAllProd()
     *  @apiName hideAllProd()
     *  @apiGroup AdminProductsController
     *  @apiDescription Update active status of all product
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function hideAllProd()
    {
        Product::where('id', '>', 0)->update(['status_active' => 2]);

        return back();
    }

    /** @api {public} hideProdId() hideProdId()
     *  @apiName hideProdId()
     *  @apiGroup AdminProductsController
     *  @apiParam {int} id Id of product
     *  @apiDescription Update active status of product
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function hideProdId($id)
    {
        Product::where('id', $id)->update(['status_active' => 2]);

        return ['status' => 1];
    }

    /** @api {public} hideAllProdCategory() hideAllProdCategory()
     *  @apiName hideAllProdCategory()
     *  @apiGroup AdminProductsController
     *  @apiParam {int} id Id of products category
     *  @apiDescription Update active status of products of category
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function hideAllProdCategory($id)
    {
        $categories_id = $this->recursiveCategoriesID(Category::all(), $id);

        Product::whereIn('category_id', $categories_id)->update(['status_active' => 2]);

        return back();
    }

    /** @api {public} approveAllProd() approveAllProd()
     *  @apiName approveAllProd()
     *  @apiGroup AdminProductsController
     *  @apiDescription Update approved status of all product
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function approveAllProd()
    {
        Product::where('id', '>', 0)->update(['status_approved' => 1]);

        return back();
    }

    /** @api {public} approveProdId() approveProdId()
     *  @apiName approveProdId()
     *  @apiGroup AdminProductsController
     *  @apiParam {int} id Id of product
     *  @apiDescription Update approved status of product
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function approveProdId($id)
    {
        Product::where('id', $id)->update(['status_approved' => 1]);

        return ['status' => 1];
    }

    /** @api {public} unapproveAllProdCategory() unapproveAllProdCategory()
     *  @apiName unapproveAllProdCategory()
     *  @apiGroup AdminProductsController
     *  @apiParam {int} id Id of products category
     *  @apiDescription Update approved status of products of category
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function approveAllProdCategory($id)
    {
        $categories_id = $this->recursiveCategoriesID(Category::all(), $id);

        Product::whereIn('category_id', $categories_id)->update(['status_approved' => 1]);

        return back();
    }

    /** @api {public} unapproveProdId() unapproveProdId()
     *  @apiName unapproveProdId()
     *  @apiGroup AdminProductsController
     *  @apiParam {int} id Id of product
     *  @apiDescription Update approved status of product
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function unapproveProdId($id)
    {
        Product::where('id', $id)->update(['status_approved' => 0]);

        return ['status' => 1];
    }

    /** @api {public} recommendProdId() recommendProdId()
     *  @apiName recommendProdId()
     *  @apiGroup AdminProductsController
     *  @apiParam {int} id Id of product
     *  @apiDescription Update recommend status of product
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function recommendProdId($id)
    {
        Product::where('id', $id)->update(['featured' => 1]);

        return ['status' => 1];
    }

    /** @api {public} notRecommendProdId() notRecommendProdId()
     *  @apiName notRecommendProdId()
     *  @apiGroup AdminProductsController
     *  @apiParam {int} id Id of product
     *  @apiDescription Update recommend status of product
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function notRecommendProdId($id)
    {
        Product::where('id', $id)->update(['featured' => 0]);

        return ['status' => 1];
    }

    /** @api {public} recommendProdAll() recommendProdAll()
     *  @apiName recommendProdAll()
     *  @apiGroup AdminProductsController
     *  @apiDescription Update recommend status of all product
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function recommendProdAll()
    {
        Product::where('id', '>', 0)->update(['featured' => 1]);

        return back();
    }

    /** @api {public} notRecommendProdAll() notRecommendProdAll()
     *  @apiName notRecommendProdAll()
     *  @apiGroup AdminProductsController
     *  @apiDescription Update recommend status of all product
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function notRecommendProdAll()
    {
        Product::where('id', '>', 0)->update(['featured' => 0]);

        return back();
    }

    /** @api {public} changeDiscountProdId() changeDiscountProdId()
     *  @apiName changeDiscountProdId()
     *  @apiGroup AdminProductsController
     *  @apiParam {object} request Request object
     *  @apiParam {dbl} request.value Discount value
     *  @apiParam {int} id Id of product
     *  @apiDescription Update discount of product
     *  @apiSuccessExample Success-Response:
     *  ['status' => 1]
     */
    public function changeDiscountProdId(Request $request, $id)
    {
        Product::where('id', $id)->update(['discount' => $request['value']]);

        return ['status' => 1];
    }

    /** @api {public} changeDiscountProdAll() changeDiscountProdAll()
     *  @apiName changeDiscountProdAll()
     *  @apiGroup AdminProductsController
     *  @apiParam {object} request Request object
     *  @apiParam {dbl} request.discount_all Discount value
     *  @apiDescription Update discount of all products
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function changeDiscountProdAll(Request $request)
    {
        Product::where('id', '>', 0)->update(['discount' => $request['discount_all']]);

        return back();
    }

    /** @api {public} unapproveAllProd() unapproveAllProd()
     *  @apiName unapproveAllProd()
     *  @apiGroup AdminProductsController
     *  @apiDescription Update approved status of all product
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function unapproveAllProd()
    {
        Product::where('id', '>', 0)->update(['status_approved' => 0]);

        return back();
    }

    /** @api {public} unapproveAllProdCategory() unapproveAllProdCategory()
     *  @apiName unapproveAllProdCategory()
     *  @apiGroup AdminProductsController
     *  @apiParam {int} id Id of products category
     *  @apiDescription Update approved status of products of category
     *  @apiSuccessExample Success-Response:
     *  back()
     */
    public function unapproveAllProdCategory($id)
    {
        $categories_id = $this->recursiveCategoriesID(Category::all(), $id);

        Product::whereIn('category_id', $categories_id)->update(['status_approved' => 0]);

        return back();
    }

    /** @api {public} getProductData() getProductData()
     *  @apiName getProductData()
     *  @apiGroup AdminProductsController
     *  @apiParam {int} id Id of product
     *  @apiDescription Get product data
     *  @apiSuccess {object} product Object product data
     */
    public function getProductData($id)
    {
        return Product::where('id', $id)->with('prices', 'previews', 'user')->first();
    }

    /** @api {public} deleteAllBaseProd() deleteAllBaseProd()
     *  @apiName deleteAllBaseProd()
     *  @apiGroup AdminProductsController
     *  @apiDescription Delete all base products
     *  @apiSuccessExample Success-Response:
     *  ['status'=>'1']
     */
    public function deleteAllBaseProd()
    {
        $this->deleteAllImgAllProd(1);
        return ['status' => 1];
    }

    /** @api {public} deleteBaseProd() deleteBaseProd()
     *  @apiName deleteBaseProd()
     *  @apiGroup AdminProductsController
     *  @apiParam {int} id Id of base product
     *  @apiDescription Delete product from id
     *  @apiSuccessExample Success-Response:
     *  ['status'=>'1']
     */
    public function deleteBaseProd($id)
    {
        Product::where('is_base', 1)->where('id', $id)->delete();

        $this->deleteAllImgProd($id);

        return ['status' => 1];
    }

    /** @api {public} getBigPreview() getBigPreview()
     *  @apiName getBigPreview()
     *  @apiGroup AdminProductsController
     *  @apiParam {int} id Id of product
     *  @apiDescription Get Product Preview
     *  @apiSuccess {object} product Object ProductPreview
     */
    public function getBigPreview($id)
    {
        return ProductPreview::where('product_id', $id)->get();
    }

    /** @api {public} editBaseProd() editBaseProd()
     * @apiName editBaseProd()
     * @apiGroup AdminProductsController
     *  @apiParam {int} id Id of product
     * @apiDescription  Return view for edit template
     */
    public function editBaseProd($id)
    {
        $this->data['icons'] = IconsGroup::where('id', '>', 1)->with('images')->get();
        $this->data['base_seo'] = BaseSEOData::all();
        $groups = UserGroup::where('base_group_id', '>', '3')->pluck('id')->toArray();

        $this->data['users'] = User::whereIn('group_id', $groups)->get();
        $this->data['template'] = Product::where('id', $id)->with('imgComponents', 'textComponents', 'previews')->first();
        $this->data['prod_categories'] = Category::all();
        $this->data['base_products'] = Product::where('is_base', 1)->with('preview', 'category', 'previews')->get();
        return view('admin.pages.product.template_edit', $this->data);
    }

    /** @api {public} editProduct() editProduct()
     * @apiName editProduct()
     * @apiGroup AdminProductsController
     *  @apiParam {int} id Id of product
     * @apiDescription  Return view for edit product
     */
    public function editProduct($id)
    {
        $this->data['icons'] = IconsGroup::where('id', '>', 0)->with('images')->get();
        $this->data['base_seo'] = BaseSEOData::all();

        $groups = UserGroup::where('base_group_id', '>', '3')->pluck('id')->toArray();
        $this->data['users'] = User::whereIn('group_id', $groups)->get();
        $this->data['product'] = Product::where('id', $id)->with('prices', 'previews')->first();
        $this->data['users'] = User::whereIn('group_id', $groups)->get();

        $this->data['page_categories'] = Category::all();
        $tags_id = TagsOfProduct::where('product_id', $id)->pluck('category_teg_id')->toArray();
        $this->data['tags_prod'] = CategoryTeg::whereIn('id',$tags_id)->pluck('name')->toArray();
        $this->data['category_data'] = Category::where('id', $this->data['product']->category_id)->with('parameters', 'parameters.tags')->first();

        return view('admin.pages.product.edit_product', $this->data);
    }
}
