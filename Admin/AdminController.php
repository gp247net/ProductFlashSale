<?php
#App\GP247\Plugins\ProductFlashSale\Admin\AdminController.php

namespace App\GP247\Plugins\ProductFlashSale\Admin;

use GP247\Core\Controllers\RootAdminController;
use App\GP247\Plugins\ProductFlashSale\AppConfig;
use App\GP247\Plugins\ProductFlashSale\Models\PluginModel;
use GP247\Shop\Models\ShopProductPromotion;
use GP247\Shop\Models\ShopProduct;
use Illuminate\Support\Facades\Validator;
class AdminController extends RootAdminController
{
    public $plugin;

    public function __construct()
    {
        parent::__construct();
        $this->plugin = new AppConfig;
    }
    public function index()
    {
        $data = [
            'title' => trans($this->plugin->appPath.'::lang.admin.list'),
            'title_action' => '<i class="fa fa-plus" aria-hidden="true"></i> ' . trans($this->plugin->appPath.'::lang.admin.add_new_title'),
            'subTitle' => '',
            'icon' => 'fa fa-indent',
            'urlDeleteItem' => gp247_route_admin('admin_product_flash_sale.delete'),
            'removeList' => 0, // 1 - Enable function delete list item
            'buttonRefresh' => 0, // 1 - Enable button refresh
            'buttonSort' => 0, // 1 - Enable button sort
            'css' => '', 
            'js' => '',
            'url_action' => gp247_route_admin('admin_product_flash_sale.create'),
        ];

        $listTh = [
            'product_id' => trans($this->plugin->appPath.'::lang.admin.product'),
            'stock' => trans($this->plugin->appPath.'::lang.admin.stock'),
            'sold' => trans($this->plugin->appPath.'::lang.admin.sold'),
            'sort' => trans($this->plugin->appPath.'::lang.admin.sort'),
            'date_start' => trans($this->plugin->appPath.'::lang.admin.date_start'),
            'date_end' => trans($this->plugin->appPath.'::lang.admin.date_end'),
            'price_promotion' => trans($this->plugin->appPath.'::lang.admin.price_promotion'),
            'status_promotion' => trans($this->plugin->appPath.'::lang.admin.status_promotion'),
            'action' => trans($this->plugin->appPath.'::lang.admin.action'),
        ];
        $dataTmp = (new PluginModel)->getAllProductFlashSale();

        $dataTr = [];
        foreach (($dataTmp ?? []) as $key => $row) {
            $product = ShopProduct::find($row['product_id']);
            $productUrl = $product ? $product->getUrl() : '#';
            $productImg = $product ? gp247_image_render(gp247_file($product['image']), '50px', '50px') : '';
            $editUrl = gp247_route_admin('admin_product_flash_sale.edit', ['id' => $row['id']]);
            $actionHtml = '<a href="'.$editUrl.'"><span title="'.trans($this->plugin->appPath.'::lang.admin.edit').'" type="button" class="btn btn-flat btn-primary"><i class="fa fa-edit"></i></span></a>&nbsp;';
            $actionHtml .= '<span onclick="deleteItem(\''.$row['id'].'\');"  title="'.trans($this->plugin->appPath.'::lang.admin.delete').'" class="btn btn-flat btn-danger"><i class="fas fa-trash-alt"></i></span>';
            $dataTr[$row['id']] = [
                'product_id' => '<a target=_new href="'.$productUrl.'">'.$productImg.'</a>',
                'stock' => $row['stock'],
                'sold' => $row['sold'],
                'sort' => $row['sort'],
                'date_start' => gp247_datetime_to_date($row['date_start'] ?? null),
                'date_end' => gp247_datetime_to_date($row['date_end'] ?? null),
                'price_promotion' => $row['price_promotion'],
                'status_promotion' => $row['status_promotion'] ? '<span class="badge badge-success">ON</span>' : '<span class="badge badge-danger">OFF</span>',
                'action' => $actionHtml,
            ];
        }

        $data['listTh'] = $listTh;
        $data['dataTr'] = $dataTr;
        $data['pagination'] = $dataTmp ? $dataTmp->appends(request()->except(['_token', '_pjax']))->links('gp247-core::component.pagination') : '';
        $data['resultItems'] = $dataTmp ? trans($this->plugin->appPath.'::lang.admin.result_item', ['item_from' => $dataTmp->firstItem(), 'item_to' => $dataTmp->lastItem(), 'item_total' => $dataTmp->total()]) : '';

        $data['layout'] = 'index';
        $data['appPath'] = $this->plugin->appPath;
        $data['productsList'] = (new PluginModel)->getAllProductNotGroup();
        $data['obj'] = [];
        return view($this->plugin->appPath.'::Admin')->with($data);
    }



    /**
     * Post create new item in admin
     * @return [type] [description]
     */
    public function postCreate()
    {
        $data = request()->all();

        $validator = Validator::make($data, [
            'product_id'      => 'required|unique:"'.PluginModel::class.'",product_id',
            'stock'           => 'required|numeric|min:1',
            'sort'            => 'required|numeric|min:0',
            'price_promotion' => 'required|numeric|min:1',
            'date_start'      => 'required',
            'date_end'        => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($data);
        }
        $dataInsert = [
            'product_id' => $data['product_id'],
            'stock' => (int)$data['stock'],
            'sort' => (int)$data['sort'],
        ];

        (new PluginModel)->create($dataInsert);

        (new ShopProductPromotion)->updateOrCreate(
            ['product_id' => $data['product_id']],
            [
                'price_promotion' => $data['price_promotion'],
                'date_start' => $data['date_start'],
                'date_end' => $data['date_end'],
                'status_promotion' => (!empty($data['status_promotion']) ? 1 : 0),
            ]
        );
        return redirect()->route('admin_product_flash_sale.index')->with('success', trans($this->plugin->appPath.'::lang.admin.create_success'));

    }




    /**
     * Form edit
     */
    public function edit($id)
    {
        $obj = (new PluginModel)->getProduct($id);
        if(!$obj) {
            return 'No data';
        }
        $data = [
            'title' => trans($this->plugin->appPath.'::lang.admin.list'),
            'title_action' => '<i class="fa fa-plus" aria-hidden="true"></i> ' . trans($this->plugin->appPath.'::lang.admin.edit'),
            'subTitle' => '',
            'icon' => 'fa fa-indent',
            'urlDeleteItem' => gp247_route_admin('admin_product_flash_sale.delete'),
            'removeList' => 0, // 1 - Enable function delete list item
            'buttonRefresh' => 0, // 1 - Enable button refresh
            'buttonSort' => 0, // 1 - Enable button sort
            'css' => '', 
            'js' => '',
            'url_action' => gp247_route_admin('admin_product_flash_sale.edit', ['id' => $obj['id']]),
            'obj' => $obj,
            'id' => $id,
        ];

        $listTh = [
            'product_id' => trans($this->plugin->appPath.'::lang.admin.product'),
            'stock' => trans($this->plugin->appPath.'::lang.admin.stock'),
            'sold' => trans($this->plugin->appPath.'::lang.admin.sold'),
            'sort' => trans($this->plugin->appPath.'::lang.admin.sort'),
            'date_start' => trans($this->plugin->appPath.'::lang.admin.date_start'),
            'date_end' => trans($this->plugin->appPath.'::lang.admin.date_end'),
            'price_promotion' => trans($this->plugin->appPath.'::lang.admin.price_promotion'),
            'status_promotion' => trans($this->plugin->appPath.'::lang.admin.status_promotion'),
            'action' => trans($this->plugin->appPath.'::lang.admin.action'),
        ];
        $dataTmp = (new PluginModel)->getAllProductFlashSale();

        $dataTr = [];
        foreach (($dataTmp ?? []) as $key => $row) {
            $product = ShopProduct::find($row['product_id']);
            $productUrl = $product ? $product->getUrl() : '#';
            $productImg = $product ? gp247_image_render(gp247_file($product['image']), '50px', '50px') : '';
            $editUrl = gp247_route_admin('admin_product_flash_sale.edit', ['id' => $row['id']]);
            $actionHtml = '<a href="'.$editUrl.'"><span title="'.trans($this->plugin->appPath.'::lang.admin.edit').'" type="button" class="btn btn-flat btn-primary"><i class="fa fa-edit"></i></span></a>&nbsp;';
            $actionHtml .= '<span onclick="deleteItem(\''.$row['id'].'\');"  title="'.trans($this->plugin->appPath.'::lang.admin.delete').'" class="btn btn-flat btn-danger"><i class="fas fa-trash-alt"></i></span>';
            $dataTr[$row['id']] = [
                'product_id' => '<a target=_new href="'.$productUrl.'">'.$productImg.'</a>',
                'stock' => $row['stock'],
                'sale' => $row['sold'],
                'sort' => $row['sort'],
                'date_start' => gp247_datetime_to_date($row['date_start'] ?? null),
                'date_end' => gp247_datetime_to_date($row['date_end'] ?? null),
                'price_promotion' => $row['price_promotion'],
                'status_promotion' => $row['status_promotion'] ? '<span class="badge badge-success">ON</span>' : '<span class="badge badge-danger">OFF</span>',
                'action' => $actionHtml,
            ];
        }

        $data['listTh'] = $listTh;
        $data['dataTr'] = $dataTr;
        $data['pagination'] = $dataTmp ? $dataTmp->appends(request()->except(['_token', '_pjax']))->links('gp247-core::component.pagination') : '';
        $data['resultItems'] = $dataTmp ? trans($this->plugin->appPath.'::lang.admin.result_item', ['item_from' => $dataTmp->firstItem(), 'item_to' => $dataTmp->lastItem(), 'item_total' => $dataTmp->total()]) : '';

        $data['layout'] = 'edit';
        $data['appPath'] = $this->plugin->appPath;
        $data['productsList'] = (new PluginModel)->getAllProductNotGroup();
        return view($this->plugin->appPath.'::Admin')->with($data);
    }

    /**
     * update status
     */
    public function postEdit($id)
    {
        $obj = (new PluginModel)->find($id);
        $data = request()->all();
        $validator = Validator::make($data, [
            'product_id'      => 'required|unique:"'.PluginModel::class.'",product_id,' . $id . '',
            'stock'           => 'required|numeric|min:1',
            'sort'            => 'required|numeric|min:0',
            'price_promotion' => 'required|numeric|min:1',
            'date_start'      => 'required',
            'date_end'        => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($data);
        }
    //Edit

    $dataUpdate = [
        'product_id' => $data['product_id'],
        'stock'      => (int)$data['stock'],
        'sort'       => (int)$data['sort'],
    ];

    $obj->update($dataUpdate);

    (new ShopProductPromotion)->where('product_id', $data['product_id'])
    ->update(
        [
            'price_promotion' => $data['price_promotion'],
            'date_start' => $data['date_start'],
            'date_end' => $data['date_end'],
            'status_promotion' => (!empty($data['status_promotion']) ? 1 : 0),
        ]
    );

    return redirect()->back()->with('success', gp247_language_render($this->plugin->appPath.'::lang.admin.edit_success'));

    }

    /*
    Delete list item
    Need mothod destroy to boot deleting in model
    */
    public function deleteList()
    {
        if (!request()->ajax()) {
            return response()->json(['error' => 1, 'msg' => gp247_language_render('admin.method_not_allow')]);
        } else {
            $ids = request('ids');
            $arrID = explode(',', $ids);
            PluginModel::destroy($arrID);
            return response()->json(['error' => 0, 'msg' => '']);
        }
    }

}
