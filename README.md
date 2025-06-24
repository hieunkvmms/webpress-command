# hieunk/command

**hieunk/command** là một package Artisan CLI hỗ trợ phát triển cho hệ thống [Webpress CMS](https://github.com/webpress-cms).  
Package này cung cấp các lệnh để:

- Tạo mới một Webpress Component.
- Duplicate một Component có sẵn trong core để tùy biến.

## 🛠️ Cài đặt

```bash
composer require hieunk/command --dev
```
⚠️ Yêu cầu:

Laravel >= 9.x

PHP >= 8.1

Đã tích hợp Webpress CMS
📦 Các lệnh hỗ trợ
1. Tạo mới một component Webpress
bash
Sao chép
Chỉnh sửa
php artisan webpress:make-component {name}
Tham số:

{name}: Tên component mới (Ví dụ: Banner, Slider...)

Chức năng:

Tạo một component mới với cấu trúc chuẩn trong thư mục components/{name}, bao gồm các file cơ bản như:

view.blade.php

config.php

preview.png

style.css (nếu cần)

Ví dụ:

bash
Sao chép
Chỉnh sửa
php artisan make:webpress-component Banner
2. Duplicate một component từ core
bash
Sao chép
Chỉnh sửa
php artisan duplicate:webpress-component {component} {newName?}
Tham số:

{component}: Tên component gốc trong webpress/core/components/

{newName?}: (Tuỳ chọn) Tên component mới. Nếu không truyền sẽ mặc định là {component}-custom.

Chức năng:

Sao chép một component có sẵn trong core sang thư mục components/ để bạn có thể chỉnh sửa mà không ảnh hưởng đến hệ thống gốc.

Ví dụ:

bash
Sao chép
Chỉnh sửa
php artisan webpress:duplicate-component Hero BannerCustom
Sẽ tạo ra components/BannerCustom từ core/components/Hero.

💬 Góp ý & Đóng góp
Mọi đóng góp hoặc phản hồi vui lòng gửi tại:

👉 https://github.com/hieunk-dev/command
