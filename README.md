<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://member1.linkita.id/image/logo-linkita.png" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel LinkIta

Laravel LinkIta adalah package yang mengintegrasikan [LinkIta](https://www.linkita.id/category/support/member/) untuk Laravel.

- Lihat [Dokumentasi API](https://documenter.getpostman.com/view/24874063/2s8YzWSgcv#deed1c93-7f67-4210-9efe-a7d5f405a680)


## Requirements
- [PHP >= 8.1.6](http://php.net/)
- [Laravel Framework](https://github.com/laravel/framework)

## Laravel Version Compatibility

| Laravel |

| 9.x.x   |

<h2 id="fitur">Fitur apa saja yang tersedia di package LinkIta?</h2>

-   Transfer Antar Bank
-   TopUp Emoney
-   Cek Data Bank
-   Cek Saldo
-   Cetak Struk
-   Cek Transaksi

<h2 id="testing-account"> Default Account for Testing</h2>

#### Admin

-   Email: admin@gmail.com
-   Password: password


<h2 id="download">💻 Install</h2>

1. Clone repository

```bash
git clone https://github.com/IdhamIKN/JWT-LinkIta.git
```
```
cd JWT-LinkIta
```
```
composer update
```
```
copy .env.example .env
```
2. Konfigurasi database melalui `.env`

```
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```
3. Migrasi dan symlinks

```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```
4. Jalankan website

```bash
php artisan serve
```


<h2 id="dukungan">💌 Support Me</h2>

<p>
Kamu bisa mendukung aku di platform Trakteer! Dukungan kamu akan sangat berarti. Namun, dengan kamu memberikan <i>star</i> pada <i>project</i> ini juga sudah sangat cukup kok~!
</p>

<a href="https://saweria.co/idhamIKN" target="_blank"><img id="wse-buttons-preview" src="https://saweria.co/widgets/qr?streamKey=bf34f3df16e7933de1ac11444b7e92f6" height="40" style="border:0px;height:40px;" alt="Trakteer Saya"></a>

<h2 id="kontribusi">🤝 Contributing</h2>

<p>
<i>Contributions, issues and feature requests</i> sangat diapresiasi karena aplikasi ini jauh dari kata sempurna. Jangan ragu untuk melakukan <i>pull request</i> dan membuat perubahan pada <i>project</i> ini, yaaa!
</p>

<h2 id="lisensi">📝 License</h2>

<p>LinkIta is open-sourced software licensed under the MIT license.</p>

<h2 id="pembuat">🧍 Author</h2>

<p>LinkIta s dibuat oleh <a href="https://instagram.com/idhamikn?igshid=MmJiY2I4NDBkZg==">IdhamIKn</a> .</p>
