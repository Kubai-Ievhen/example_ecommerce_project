<?php

namespace App\Http\Controllers;

use App\BaseGroup;
use App\Category;
use App\InputMessage;
use App\Order;
use App\OrganizationData;
use App\PaymentOptions;
use App\Product;
use App\TimeDispatchMessages;
use App\User;
use App\UserGroup;
use App\UserProfile;
use App\UsersFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;
use Mockery\Matcher\Type;
use DB;

/**@api AdminController
 * @apiName AdminController
 * @apiGroup AdminController
 * @apiDescription App\Http\Controllers Class AdminController
 */
class AdminController extends AdminBaseController
{
    private $categories_save_id=[];
    private $categories_save=[];

    /** @api {public} addVendorJobs() addVendorJobs()
     * @apiName addVendorJobs()
     * @apiGroup AdminController
     * @apiDescription  Add job to vendor
     * @apiSuccessExample Success-Response:
     *  back()
     */
    public function addVendorJobs(Request $request){
        Order::whereIn('id', $request['vendor'])->update(['vendor_id'=>$request['user_id'], 'execution_status'=>1]);

        return back();
    }

    /** @api {public} getAllOrders() getAllOrders()
     * @apiName getAllOrders()
     * @apiGroup AdminController
     * @apiDescription  Get page with all orders.
     * @apiParam {object} request Request object
     */
    public function getAllOrders(){
        $orders= Order::all();
        $this->data['orders']=$orders->sortBy('id');
        $this->data['title'] = 'All Orders';
        $this->data['user_groups'] = UserGroup::all();

        return view('admin.pages.orders', $this->data);
    }

    /** @api {public} getNewOrders() getNewOrders()
     * @apiName getNewOrders()
     * @apiGroup AdminController
     * @apiDescription  Get page with all new orders.
     */
    public function getNewOrders(){
        $orders = Order::where('execution_status',0)->get();
        $this->data['orders']=$orders;
        $this->data['title'] = 'New Orders';
        $this->data['user_groups'] = UserGroup::all();

        return view('admin.pages.orders_new', $this->data);
    }

    /** @api {public} getAllOrders() getAllOrders()
     * @apiName getAllOrders()
     * @apiGroup AdminController
     * @apiDescription  Get page with all products.
     */
    public function getProducts(){
        $this->data['products'] = Product::all();
        $this->data['categories'] = Category::all();
        $this->data['user_groups'] = UserGroup::all();
        return view('admin.pages.products', $this->data);
    }

    /** @api {public} postProducts() postProducts()
     * @apiName postProducts()
     * @apiGroup AdminController
     * @apiDescription  Get json with all products.
     */
    public function postProducts(){
        $categories = Category::all();
        $products = Product::all();

        $parents = $categories->where('parent_id', 0);

        $categories_out = $this->recursiveProduct($parents, $categories, $products);

        return json_encode($products);
    }

    /** @api {public} saveProducts() saveProducts()
     * @apiName saveProducts()
     * @apiGroup AdminController
     * @apiDescription  Save new product.
     * @apiParam {object} request Request object
     * @apiSuccess {bool} status Status of operation.
     * @apiSuccessExample Success-Response:
     *  1
     */
    public function saveProducts(Request $request){
        $categories = json_decode($request['data']);

        foreach ($categories as $category) {
            $this->categories_save_id[] = $category->id;
            $this->categories_save[] = [
                'id' => $category->id,
                'name' => $category->text,
                'parent_id' => 0,
            ];

            if (isset($category->children)) {
                $this->recursiveDecodeProduct($category->id, $category->children);
            }
        }
        Category::whereNotIn('id', $this->categories_save_id)->delete();

        $categories_DB = Category::all();

        foreach ($this->categories_save as $category_save){
            $category_DB=$categories_DB->where('id', $category_save['id'])->first();
            if (isset($category_DB)){
                if($category_save['name']!=$category_DB->name || $category_save['parent_id']!=$category_DB->parent_id){
                    Category::where('id',$category_save['id'])->update(['name'=>$category_save['name'], 'parent_id'=>$category_save['parent_id']]);
                }
            } else{
                DB::table('categories')->insert(['name'=>$category_save['name'], 'parent_id'=>$category_save['parent_id']]);
            }

        }

        return 1;
    }

    /**
     * @param $parents
     * @param $categories
     * @param $products
     * @return array
     */
    private function recursiveProduct($parents, $categories, $products){
        $categories_out = [];
        $i=0;

        foreach($parents as $parent){
            $categories_out[$i]['id'] = 'cat_'.$parent->id;
            $categories_out[$i]['text'] = $parent->name;
            $children = $categories->where('parent_id', $parent->id);

            $products_category = $this->getArreyProduct($products->where('category_id', $parent->id));

            if($children->count()>0){
                $categories_children = $this->recursiveProduct($children, $categories, $products);
                $categories_out[$i]['children'] = array_merge($categories_children, $products_category);
            } else{
                $categories_out[$i]['children'] = $products_category;
            }

            $i++;
        }

        return $categories_out;
    }

    /**
     * @param $products
     * @return array
     */
    private function getArreyProduct($products){
        $arr_products = [];
        $i=0;

        foreach ($products as $product){
            $arr_products[$i]['id'] = $product->id;
            $arr_products[$i]['text'] = $product->title;
            $arr_products[$i]['icon'] = '/assets/img/icon/text_1.png';
            $arr_products[$i]['a_attr'] = ['href'=>'/admin/product/'.$product->id, 'id'=>$product->id];

            $i++;
        }

        return $arr_products;
    }

    /**
     * @param $parent_id
     * @param $categories
     */
    private function recursiveDecodeProduct($parent_id, $categories){
        foreach ($categories as $category) {
            $this->categories_save_id[] = $category->id;
            $this->categories_save[] = [
                'id' => $category->id,
                'name' => $category->text,
                'parent_id' => $parent_id,
            ];

            if (isset($category->children)) {
                $this->recursiveDecodeCategories($category->id, $category->children);
            }
        }
    }
}

