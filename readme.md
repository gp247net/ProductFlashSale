## ProductFlashSale — README (EN)

### Overview
ProductFlashSale is a Flash Sale plugin for GP247 store systems. It lets you:
- Configure time-based promotional prices per product.
- Manage Flash Sale inventory independently (stock/sold) and display order (sort).
- Display Flash Sale products on a dedicated page and as a homepage block.

### Requirements
- Required: `gp247/shop`.

### Key features
- Admin:
  - Dedicated admin screen to add/edit/delete Flash Sale configurations per product.
  - Fields: `product_id`, `stock`, `sold`, `sort`, `price_promotion`, `date_start`, `date_end`, `status_promotion`.
  - Auto create/sync the promotion record (`shop_product_promotion`) on create/update.
  - Creates its own data table `shop_product_flash` on install.
- Front-end:
  - Listing page: route name `product_flash_sale.index` (URL: `/plugin/product_flash_sale/index`, multilingual if `GP247_SEO_LANG` is enabled).
  - Block shows countdown timer, sold/stock progress bar, and promotional price.
  - Only displays items within the promo time window, in stock, and with promotion enabled.
- Helpers:
  - `gp247_product_flash($limit = 8, $paginate = false)` — fetch Flash Sale products.
  - `gp247_product_flash_check_over($productId, $quantity)` — check remaining quantity.
  - `gp247_product_flash_update_stock($productId, $quantity)` — increase sold quantity.

### Processing logic
- Before an order is created: system checks remaining quantity from `shop_product_flash` (`stock` vs `sold`).
  - Reference helper: `gp247_product_flash_check_over($productId, $quantity)`.
- After the order is created: system increases `sold` value accordingly in `shop_product_flash`.
  - Reference helper: `gp247_product_flash_update_stock($productId, $quantity)`.

### Installation
See the official guide: [Extension installation guide](https://gp247.net/en/docs/user-guide-extension/guide-to-installing-the-extension.html).

Quick steps:
1) Plugin source is available at `app/GP247/Plugins/ProductFlashSale`.
2) In Admin panel:
   - Go to Extensions/Plugins.
   - Find "Product Flash Sale" and click Install.
   - Click Enable to activate it.
3) The system will:
   - Add an admin menu under the Catalog group pointing to `admin_product_flash_sale.index`.
   - Create the data table `shop_product_flash`.
4) Configure Flash Sale for products in admin:
   - Choose a product (not Group type), set `stock`, `sort`.
   - Set `price_promotion`, `date_start`, `date_end`, and turn on `status_promotion`.
   - Save to create/sync the promotion info.
5) Clear system cache (if enabled) to apply the latest config.

### Front-end usage
- Flash Sale listing page: use route `product_flash_sale.index` in your theme.

Blade example to add a link:
```blade
{{-- Link to flash sale page --}}
<a href="{{ route('product_flash_sale.index') }}">Flash Sale</a>
```

- Include the Flash Sale block on homepage or any position:
```blade
{{-- Include Product Flash Sale block --}}
@include('Plugins/ProductFlashSale::blocks.flash_sale')
```

- Optional (override by template and manage via admin):
  1) Copy the block view from the plugin into your current template:
     - Source: `app/GP247/Plugins/ProductFlashSale/Views/blocks/flash_sale.blade.php`
     - Destination: `app/GP247/Templates/<TEMPLATE>/blocks/flash_sale.blade.php`
  2) In admin, go to `layout_block` and add the `flash_sale` block to your desired position.

- Use helpers to build a custom list:
```blade
{{-- Get 8 flash sale products (no paginate) --}}
@php($products = gp247_product_flash(8))

@foreach($products as $product)
    <div>{{ $product->name }}</div>
@endforeach
```

Notes:
- The sample block is at `app/GP247/Plugins/ProductFlashSale/Views/blocks/flash_sale.blade.php`.
- Countdown uses `promotionPrice->date_end` and `jquery.countdown` (CDN).
- Display conditions: promotion enabled, current date within `date_start` → `date_end`, and `sold < stock`.

### Enable/Disable and Uninstall
- Enable/Disable from the Admin Extensions/Plugins manager.
- Uninstall removes config, menu, home widget entry, and the `shop_product_flash` table created by this plugin.

### Additional info
- Main source: `App\GP247\Plugins\ProductFlashSale\`.
- Routes:
  - Front: `GET /plugin/product_flash_sale/index` → name `product_flash_sale.index`.
  - Admin: `GP247_ADMIN_PREFIX/product_flash_sale/*` with CRUD actions.
- View/Translation namespace: `Plugins/ProductFlashSale` (e.g. `Plugins/ProductFlashSale::blocks.flash_sale`, `Plugins/ProductFlashSale::lang.*`).

### Version
- 1.0.0: Initial release.

