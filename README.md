# Laravel-Bakes

## 🛠️ **Project Status: In Development**

### ⚠️ **This project is currently under active development and is not yet ready for production use.**
Features may be incomplete or unstable. Contributions and feedback are welcome!

## Project Description

**Laravel-Bake** is a powerful Laravel plugin designed to streamline database schema-based code generation, inspired by CakePHP 2.0's `bake` CLI tool. It allows developers to quickly scaffold models, controllers, and CRUD pages directly from database tables using a simple Artisan command. This project is proudly sponsored by [Radeus Labs](https://radeuslabs.com), a company dedicated to supporting the open-source community.

## About Radeus Labs

![Radeus Labs Logo](https://radeuslabs.com/wp-content/uploads/2025/01/logo-radeus-labs-footer-New.png)

[Radeus Labs](https://radeuslabs.com) is a leader in providing high-quality **SATCOM systems** and **rack-mount servers** tailored for military applications. Their cutting-edge solutions ensure reliable communication and robust computing power in the most demanding environments. Radeus Labs is committed to fostering innovation and giving back to the open-source community, which is why they sponsored the development of Laravel-Bake by Brandon Tanner.

## Features

- Generate Laravel models, controllers, and CRUD pages from database table schemas.
- Inspired by [CakePHP 2.0's Bake CLI](https://book.cakephp.org/2/en/console-and-shells/code-generation-with-bake.html).
- Currently generates CRUD pages using **Bootstrap 5**.
- Planned support for **Tailwind CSS**, **ExtJS 4.2.2**, and a theme mimicking **CakePHP 2.x default baked views**.
- Currently supports **PostgreSQL**, with plans for broader database support in the future.
- Easy integration with Laravel via Composer.

## Installation

Since Laravel-Bake is not yet available on Packagist, you can install it by adding the repository directly to your `composer.json`.

### Step 1: Add the Repository

Modify your `composer.json` to include the following:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/pyrite357/laravel-bake.git"
    }
]
```

### Step 2: Require the Package

Run the following Composer command to install Laravel-Bake:

```bash
composer require pyrite357/laravel-bake
```

### Step 3: Register the Service Provider

Add the service provider to the `providers` array in `config/app.php`:

```php
'providers' => [
    // Other providers...
    Pyrite357\LaravelBake\LaravelBakeServiceProvider::class,
]
```

## Usage

Laravel-Bake provides a simple Artisan command to generate code based on your database schema:

```bash
php artisan cake:bake <schema.table>
```

### Example

To generate code for a table named `documents` in the `public` schema:

```bash
php artisan cake:bake public.documents
```

For a table named `sales_orders` in the `erp_system` schema:

```bash
php artisan cake:bake erp_system.sales_orders
```

### Notes

- The `<schema.table>` format follows the PostgreSQL convention (e.g., `schema_name.table_name`).
- Currently, Laravel-Bake only supports **PostgreSQL**. Support for other databases (MySQL, SQLite, etc.) is planned for future releases.
- The generated CRUD pages use **Bootstrap 5** for styling. Future updates will include support for **Tailwind CSS**, **ExtJS 4.2.2**, and a theme that mimics the **CakePHP 2.x default baked views**.
- The generated code is inspired by the functionality of [CakePHP 2.0's Bake CLI](https://book.cakephp.org/2/en/console-and-shells/code-generation-with-bake.html), creating Laravel-compatible models, controllers, and views.

## Supported Databases

- **PostgreSQL** (current)
- MySQL, SQLite, and others (planned for future releases)

## Supported Front-End Frameworks

- **Bootstrap 5** (current)
- **ExtJS 4.2.2** (planned)
- **Tailwind CSS** (planned)
- **CakePHP 2.x default baked views theme** (planned)

## License

Laravel-Bake is open-source software licensed under the [MIT License](LICENSE). This permissive license allows you to use, modify, and distribute the plugin freely, aligning with Radeus Labs' commitment to supporting the open-source community.

## Contributing

We welcome contributions to Laravel-Bake! To contribute:

1. Fork the repository.
2. Create a new branch (`git checkout -b feature/your-feature`).
3. Commit your changes (`git commit -m 'Add your feature'`).
4. Push to the branch (`git push origin feature/your-feature`).
5. Open a pull request.

Please ensure your code follows the [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards and includes appropriate tests.

## Credits

- **Author**: Brandon Tanner
- **Sponsor**: [Radeus Labs](https://radeuslabs.com)

Thank you to Radeus Labs for their generous support in making this project possible!

## Contact

For issues, feature requests, or questions, please open an issue on the [GitHub repository](https://github.com/your-username/laravel-bake) or contact the maintainer at [your-email@example.com].

---
Happy Baking! 🍰
