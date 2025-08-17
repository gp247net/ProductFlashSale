<?php
use Illuminate\Support\Facades\Route;

$config = file_get_contents(__DIR__.'/gp247.json');
$config = json_decode($config, true);

if (gp247_extension_check_active($config['configGroup'], $config['configKey'])) {

    $langUrl = GP247_SEO_LANG ?'{lang?}/' : '';
    $suffix = GP247_SUFFIX_URL;

    // Front routes

    // Optional internal front routes
    Route::group(
        [
            'middleware' => GP247_FRONT_MIDDLEWARE,
            'prefix'    => $langUrl.'plugin/product_flash_sale',
            'namespace' => 'App\\GP247\\Plugins\\ProductFlashSale\\Controllers',
        ],
        function () {
            Route::get('index', 'FrontController@index')
                ->name('product_flash_sale.index');
        }
    );
}

// Admin routes
Route::group(
    [
        'prefix' => GP247_ADMIN_PREFIX.'/product_flash_sale',
        'middleware' => GP247_ADMIN_MIDDLEWARE,
        'namespace' => 'App\\GP247\\Plugins\\ProductFlashSale\\Admin',
    ], 
    function () {
        Route::get('/', 'AdminController@index')->name('admin_product_flash_sale.index');
        Route::post('/create', 'AdminController@postCreate')->name('admin_product_flash_sale.create');
        Route::get('/edit/{id}', 'AdminController@edit')->name('admin_product_flash_sale.edit');
        Route::post('/edit/{id}', 'AdminController@postEdit')->name('admin_product_flash_sale.edit');
        Route::post('/delete', 'AdminController@deleteList')->name('admin_product_flash_sale.delete');
    }
);
