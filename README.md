# Store Cart

A Laravel e-commerce application built with Lunar.

## Setup

### Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL or PostgreSQL database

### Installation

1. Clone the repository and navigate to the project directory

2. Install PHP dependencies:
```bash
composer install
```

3. Copy the environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Configure your database in the `.env` file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=store_cart
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. Run migrations:
```bash
php artisan migrate
```


## Lunar Setup

This project uses [Lunar](https://lunarphp.io/) for e-commerce functionality.

### Install Lunar

Lunar should already be installed via Composer. If you need to publish Lunar configuration:

```bash
php artisan vendor:publish --tag=lunar
```

### Lunar Database Setup

Run Lunar migrations (already included in step 7 above if you've run `php artisan migrate`).

## Database Seeding

### Seed All Data

To run all seeders:

```bash
php artisan db:seed
```

## Running the Application

Start the development server:

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.


**Admin Credentials:**
- Email: `admin@example.com`
- Password: `password`

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
