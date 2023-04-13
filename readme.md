# OVH bills importer

This is a simple PHP script that:

-   scrape new OVH invoices for the current month,
-   store them locally in the `invoices` folder following the `invoices/service_name/year/month/date-OVH-original_id-XXEURXX.pdf` pattern,
-   send alerts to provided e-mail addresses with the new invoice attached.

## Requirements

-   PHP 7.4
-   Composer
-   MySQL database
-   SMTP credentials (host, port, username, password)
-   OVH API credentials (application key, application secret, consumer key) and endpoint

## Installation

Install **OVH SDK for PHP** & **PHPMailer** with Composer in the terminal:

```bash
composer install
```

Import the `ovh_invoice.sql` file (in /docs) into your MySQL database.

## Configuration

Copy the `config.ini.dist` file and rename it to `config.ini`.

Fill in the `config.ini` file with all needed credentials.

Remove unecessary services from the `$requestedServices` variable's array in `ovh_bill_importer.php` file:

```php
$requestedServices = ['OVH', 'KIM', 'SYS'];

// e.g. if you only want OVH invoices, remove the other services
$requestedServices = ['OVH'];
```

## Usage

In the terminal, in the folder where the script is located, run `php ovh_bill_importer.php`.
