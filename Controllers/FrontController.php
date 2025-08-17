<?php
#App\GP247\Plugins\ProductFlashSale\Controllers\FrontController.php
namespace App\GP247\Plugins\ProductFlashSale\Controllers;

use App\GP247\Plugins\ProductFlashSale\AppConfig;
use GP247\Front\Controllers\RootFrontController;
class FrontController extends RootFrontController
{
    public $plugin;

    public function __construct()
    {
        parent::__construct();
        $this->plugin = new AppConfig;
    }


    /**
     * Process front flash sale
     *
     * @param [type] ...$params
     * @return void
     */
    public function index(...$params) 
    {
        if (GP247_SEO_LANG) {
            $lang = $params[0] ?? '';
            gp247_lang_switch($lang);
        }
        return $this->_flashSaleProcess();
    }


    /**
     * flashSaleProcess product
     * @return [view]
     */
    private function _flashSaleProcess()
    {
        $filter_sort = request('filter_sort') ?? '';
        if (function_exists('gp247_product_flash')) {
            $products = gp247_product_flash(gp247_config('item_list'), $paginate = true);
        } else {
            $products = [];
        }
        gp247_check_view($this->GP247TemplatePath . '.screen.shop_product_list');
        return view(
            $this->GP247TemplatePath . '.screen.shop_product_list',
            array(
                'title' => gp247_language_render($this->plugin->appPath.'::lang.front.flash_title'),
                'products' => $products,
                'layout_page' => 'shop_product_list',
                'filter_sort' => $filter_sort,
            )
        );
    }
}
