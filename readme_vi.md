## ProductFlashSale — Tài liệu giới thiệu (VI)

### Tổng quan
ProductFlashSale là plugin khuyến mãi chớp nhoáng (Flash Sale) cho hệ thống cửa hàng của GP247. Plugin giúp bạn:
- Thiết lập mức giá khuyến mãi theo thời gian cho từng sản phẩm.
- Quản lý tồn kho Flash Sale độc lập (stock/sold) và thứ tự hiển thị (sort).
- Hiển thị danh sách sản phẩm đang Flash Sale ở trang riêng và block trên trang chủ.

### Yêu cầu
- Bắt buộc cài đặt: `gp247/shop`.

### Tính năng chính
- Quản trị:
  - Trang quản trị riêng: thêm/sửa/xóa cấu hình Flash Sale cho sản phẩm.
  - Trường cấu hình: `product_id`, `stock`, `sold`, `sort`, `price_promotion`, `date_start`, `date_end`, `status_promotion`.
  - Tự tạo/đồng bộ bản ghi khuyến mãi (`shop_product_promotion`) khi thêm/sửa Flash Sale.
  - Tạo bảng dữ liệu riêng `shop_product_flash` khi cài đặt plugin.
- Front-end:
  - Trang danh sách: route tên `product_flash_sale.index` (URL: `/plugin/product_flash_sale/index`, có hỗ trợ đa ngôn ngữ nếu bật `GP247_SEO_LANG`).
  - Block hiển thị Flash Sale kèm bộ đếm thời gian, tiến độ bán (sold/stock), giá khuyến mãi.
  - Chỉ hiển thị hàng còn trong khung thời gian, còn hàng và đang bật khuyến mãi.
- Helper sẵn có:
  - `gp247_product_flash($limit = 8, $paginate = false)` — lấy danh sách sản phẩm Flash Sale.
  - `gp247_product_flash_check_over($productId, $quantity)` — kiểm tra vượt số lượng còn lại.
  - `gp247_product_flash_update_stock($productId, $quantity)` — tăng số đã bán (sold).

### Logic xử lý
- Trước khi đơn hàng được tạo: hệ thống kiểm tra số lượng còn lại của sản phẩm Flash Sale dựa trên bảng `shop_product_flash` (trường `stock` và `sold`).
  - Tham chiếu helper: `gp247_product_flash_check_over($productId, $quantity)`.
- Khi đơn hàng được tạo: hệ thống tăng giá trị `sold` tương ứng trong bảng `shop_product_flash`.
  - Tham chiếu helper: `gp247_product_flash_update_stock($productId, $quantity)`.

### Hướng dẫn cài đặt
Tham khảo chi tiết theo tài liệu chính thức: [Hướng dẫn cài đặt Extension](https://gp247.net/vi/docs/user-guide-extension/guide-to-installing-the-extension.html).

Tóm tắt các bước:
1) Chuẩn bị mã nguồn plugin (đã có sẵn tại `app/GP247/Plugins/ProductFlashSale`).
2) Vào trang quản trị:
   - Mở mục Extensions/Plugins (trình quản lý extension).
   - Tìm "Product Flash Sale" và nhấn Install.
   - Sau khi cài đặt, nhấn Enable để kích hoạt.
3) Hệ thống sẽ:
   - Thêm menu quản trị dưới nhóm Catalog trỏ đến `admin_product_flash_sale.index`.
   - Tự tạo bảng dữ liệu `shop_product_flash`.
4) Cấu hình Flash Sale cho sản phẩm tại trang quản trị:
   - Chọn sản phẩm (không thuộc nhóm Group), nhập `stock`, `sort`.
   - Thiết lập `price_promotion`, `date_start`, `date_end`, bật `status_promotion`.
   - Lưu để tạo/đồng bộ thông tin khuyến mãi.
5) Xóa cache hệ thống (nếu bật cache) để đảm bảo nhận cấu hình mới.

### Sử dụng giao diện (Front-end)
- Trang danh sách Flash Sale: dùng route `product_flash_sale.index` để gắn link trong theme.

Ví dụ chèn link trong Blade:
```blade
{{-- Link to flash sale page --}}
<a href="{{ route('product_flash_sale.index') }}">Flash Sale</a>
```

- Chèn block Flash Sale vào trang chủ hoặc vị trí mong muốn:
```blade
{{-- Include Product Flash Sale block --}}
@include('Plugins/ProductFlashSale::blocks.flash_sale')
```

- Tùy chọn (override theo template và quản lý qua admin):
  1) Sao chép file view block từ plugin sang template hiện tại:
     - Nguồn: `app/GP247/Plugins/ProductFlashSale/Views/blocks/flash_sale.blade.php`
     - Đích: `app/GP247/Templates/{TEMPLATE}/blocks/flash_sale.blade.php`
  2) Vào trang quản trị, mục `layout_block`, thêm block `flash_sale` vào vị trí mong muốn của giao diện.

- Sử dụng helper để tự tùy biến danh sách:
```blade
{{-- Get 8 flash sale products (no paginate) --}}
@php($products = gp247_product_flash(8))

@foreach($products as $product)
    <div>{{ $product->name }}</div>
@endforeach
```

Ghi chú:
- Block mẫu của plugin nằm tại `app/GP247/Plugins/ProductFlashSale/Views/blocks/flash_sale.blade.php`.
- Bộ đếm thời gian dựa theo `promotionPrice->date_end` của sản phẩm và dùng `jquery.countdown` (CDN).
- Điều kiện hiển thị: khuyến mãi đang bật, ngày hiện tại nằm trong khoảng `date_start` → `date_end`, và `sold < stock`.

### Gỡ cài đặt / Bật tắt
- Disable/Enable: thực hiện tại trang quản trị Extensions/Plugins.
- Uninstall: xóa cấu hình, menu, home widget và bảng `shop_product_flash` do plugin tạo.
