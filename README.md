<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Dokumentasi API

Penjelasan terkait pembuatan API Laravel ini dengan beberapa fitur yang saya sudah selesaikan sesuai dengan ketentuan yang sudah ditentukan. Berikut adalah dokumentasi API yang sudah saya buat dan penjelasan singkat mengenai fitur tersebut.

### 1. Data Master

-   Master User memiliki 1 tabel untuk menampung data user. Data yang diinputkan adalah nama user, email, dan password.
-   Master Post memiliki 1 tabel untuk menampung data post. Data yang diinputkan adalah title, content, seo_title, seo_description, meta_keyword, status dan category.
-   Master Category memiliki 1 tabel untuk menampung data category. Data yang diinputkan adalah nama category dan description.
-   Master Bookmark dimana ini akan menyimpan data post yang telah di bookmark oleh user saat ini atau sedang login.
-   Semua fitur di master ini memiliki fitur create, update, delete, dan show.

### 2. Fitur Laravel

-   Fitur Login dan Register dengan menggunakan middleware untuk membatasi akses halaman yang tidak boleh diakses oleh user yang tidak login. untuk fitur login ini akan mengecek apakah user tersebut sudah verified email atau belum dan fitur register ini akan membuat user baru dan mengirimkan email verifikasi ke email yang telah diinputkan.

-   Upload Image digunakan pada fitur create post, update post dengan menggunakan fitur upload image Spatie Media Library.

### 3. Fitur Enhancement atau tambahan

-   Fitur forget password yang digunakan untuk mengatur password baru jika user lupa passwordnya dengan mengirimkan link email ke email user yang telah diinputkan. saya menggunakan mailtrap sebagai email service provider.

-   Fitur Email Notification yang digunakan untuk memberikan notifikasi kepada user yang telah melakukan register atau mendaftar sebagai user baru.

-   Fitur Email Verification yang digunakan untuk memverifikasi email user yang telah mendaftar sebagai user baru untuk dapat login ke dalam sistem. karena ada middleware yang membatasi akses halaman yang tidak boleh diakses oleh user yang tidak terverified.

-   Fitur Cachce yang digunakan untuk mempercepat proses pengambilan data dari database disini saya memakainya pada CRUD Data Master yang dimana setiap kali user hit endpoint tersebut maka pertama kali akan dicek database setelah itu akan disimpan di cache sehingga ketika user hit endpoint tersebut lagi maka akan langsung diambil dari cache. Jika data di cache sudah melebihi dari 5 menit maka akan dihapus dari cache dan diambil dari database lagi.

-   Fitur Event Listener dan Queue dimana ini berkesinambungan dalam proses pengiriman email verifikasi dan email notifikasi. jadi saya membuat event untuk reminder user jika telah mendaftar maka selanjutnya akan dikirimkan email verifikasi jika lebih dari 5 menit maka akan dikirimkan email notifikasi seacara Queue dengan Jobs Database.
