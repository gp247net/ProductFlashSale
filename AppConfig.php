<?php
#App\GP247\Plugins\ProductFlashSale\AppConfig.php
namespace App\GP247\Plugins\ProductFlashSale;

use App\GP247\Plugins\ProductFlashSale\Models\PluginModel;
use GP247\Core\Models\AdminConfig;
use GP247\Core\Models\AdminHome;
use GP247\Core\Models\AdminMenu;
use GP247\Core\ExtensionConfigDefault;
use Illuminate\Support\Facades\File;

class AppConfig extends ExtensionConfigDefault
{
    public function __construct()
    {
        //Read config from gp247.json
        $config = file_get_contents(__DIR__.'/gp247.json');
        $config = json_decode($config, true);
        $this->configGroup = $config['configGroup'];
        $this->configKey = $config['configKey'];
        $this->configCode = $config['configCode'];
        $this->requireCore = $config['requireCore'] ?? [];
        $this->requirePackages = $config['requirePackages'] ?? [];
        $this->requireExtensions = $config['requireExtensions'] ?? [];
        //Path
        $this->appPath = $this->configGroup . '/' . $this->configKey;
        //Language
        $this->title = trans($this->appPath.'::lang.title');
        //Image logo or thumb
        $this->image = $this->appPath.'/'.$config['image'];
        //
        $this->version = $config['version'];
        $this->auth = $config['auth'];
        $this->link = $config['link'];
    }

    public function install()
    {
        $check = AdminConfig::where('key', $this->configKey)
            ->where('group', $this->configGroup)->first();
        if ($check) {
            $return = ['error' => 1, 'msg' =>  gp247_language_render('admin.extension.plugin_exist')];
        } else {
            $dataInsert = [
                [
                    'group'  => $this->configGroup,
                    'code'    => $this->configCode,
                    'key'    => $this->configKey,
                    'sort'   => 0,
                    'store_id' => GP247_STORE_ID_GLOBAL,
                    'value'  => self::ON,
                    'detail' => $this->appPath.'::lang.title',
                ],
            ];
            try {
                AdminConfig::insert($dataInsert);

                $blockMarketing = AdminMenu::where('key','ADMIN_SHOP_CATALOG')->first();
                if($blockMarketing) {
                    AdminMenu::insert([
                        'sort' => 100,
                        'parent_id' => $blockMarketing->id,
                        'title' => ''.$this->appPath.'::lang.title',
                        'icon' => 'fa fa-bolt',
                        'uri' => 'route_admin::admin_product_flash_sale.index',
                        'key' => $this->configKey,
                        ]);
                }


                (new PluginModel)->installExtension();
                $return = ['error' => 0, 'msg' => gp247_language_render('admin.extension.install_success')];
            } catch (\Throwable $th) {
                $return = ['error' => 1, 'msg' => $th->getMessage()];
            }
        }
        return $return;
    }

    public function uninstall()
    {
        try {
            //Delete config
            (new AdminConfig)
                ->where('key', $this->configKey)
                ->orWhere('code', $this->configKey.'_config')
                ->delete();
            //Delete home
            AdminHome::where('extension', $this->appPath)->delete();
            //Delete menu
            AdminMenu::where('key', $this->configKey)->delete();
            (new PluginModel)->uninstallExtension();
            $return = ['error' => 0, 'msg' => gp247_language_render('admin.extension.uninstall_success')];
        } catch (\Throwable $e) {
            $return = ['error' => 1, 'msg' => $e->getMessage()];
        }

        return $return;
    }
    
    public function enable()
    {
        $process = (new AdminConfig)
            ->where('group', $this->configGroup)
            ->where('key', $this->configKey)
            ->update(['value' => self::ON]);

        AdminHome::where('extension', $this->appPath)->update(['status' => 1]);

        if (!$process) {
            $return = ['error' => 1, 'msg' => gp247_language_render('admin.extension.action_error', ['action' => 'Enable'])];
        }
        $return = ['error' => 0, 'msg' => gp247_language_render('admin.extension.enable_success')];
        return $return;
    }

    public function disable()
    {
        $process = (new AdminConfig)
            ->where('group', $this->configGroup)
            ->where('key', $this->configKey)
            ->update(['value' => self::OFF]);
        if (!$process) {
            $return = ['error' => 1, 'msg' => gp247_language_render('admin.extension.action_error', ['action' => 'Disable'])];
        }
        $return = ['error' => 0, 'msg' => gp247_language_render('admin.extension.disable_success')];
        AdminHome::where('extension', $this->appPath)->update(['status' => 0]);

        return $return;
    }

    public function removeStore($storeId = null)
    {
        // code here
    }

    public function setupStore($storeId = null)
    {
       // code here
    }


    // Process when click button plugin in admin    

    public function clickApp()
    {
        return redirect(gp247_route_admin('admin_product_flash_sale.index'));
    }

    public function getData()
    {
        $arrData = [
            'title'      => $this->title,
            'key'        => $this->configKey,
            'code'       => $this->configCode,
            'image'      => $this->image,
            'permission' => self::ALLOW,
            'version'    => $this->version,
            'auth'       => $this->auth,
            'link'       => $this->link,
            'value'      => 0,
            'appPath'    => $this->appPath
        ];

        return $arrData;
    }

    /**
     * Process after order success
     *
     * @param   [array]  $data  
     *
     */
    public function endApp($data = []) {
        //action after end app
    }

    public function getInfo()
    {
        $arrData = [
            'title'      => $this->title,
            'key'        => $this->configKey,
            'code'       => $this->configCode,
            'image'      => $this->image,
            'permission' => self::ALLOW,
            'version'    => $this->version,
            'auth'       => $this->auth,
            'link'       => $this->link,
            'value'      => 0,
            'appPath'    => $this->appPath
        ];

        return $arrData;
    }
}
