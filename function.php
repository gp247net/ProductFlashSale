<?php
use App\GP247\Plugins\ProductFlashSale\Models\PluginModel;

/**
 * Check over stock for flash sale product
 */
if (!function_exists('gp247_product_flash_check_over')) {
    function gp247_product_flash_check_over($productId, $quantity) {
        $tablePromotion = (new \GP247\Shop\Models\ShopProductPromotion)->getTable();
        $tableFlashSale = (new PluginModel)->getTable();
        $product = (new PluginModel)
            ->join($tablePromotion, $tablePromotion.'.product_id', '=', $tableFlashSale.'.product_id')
            ->where($tableFlashSale.'.product_id', $productId)
            ->where($tablePromotion.'.status_promotion', 1)
            ->where($tablePromotion.'.date_start', '<=', gp247_time_now())
            ->where($tablePromotion.'.date_end', '>=', gp247_time_now())
            ->first();
        if ($product && ((int)$product->stock - (int)$product->sold) < $quantity) {
            return false;
        }
        return true;
    }
}

/**
 * Update flash sale stock (increase sold)
 */
if (!function_exists('gp247_product_flash_update_stock')) {
    function gp247_product_flash_update_stock($productId, $quantity) {
        $tablePromotion = (new \GP247\Shop\Models\ShopProductPromotion)->getTable();
        $tableFlashSale = (new PluginModel)->getTable();
        $product = (new PluginModel)
            ->join($tablePromotion, $tablePromotion.'.product_id', '=', $tableFlashSale.'.product_id')
            ->where($tableFlashSale.'.product_id', $productId)
            ->where($tablePromotion.'.status_promotion', 1)
            ->where($tablePromotion.'.date_start', '<=', gp247_time_now())
            ->where($tablePromotion.'.date_end', '>=', gp247_time_now())
            ->first();
        if ($product) {
            $product->sold = $product->sold + (int)$quantity;
            $product->save();
            
        }
    }
}

/**
 * Get flash sale products
 */
if (!function_exists('gp247_product_flash')) {
    function gp247_product_flash($limit = 8, $paginate = false) {
        return (new PluginModel)->getProductFlash($limit, $paginate);
    }
}


