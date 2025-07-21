# Laravel Customer Management System

A comprehensive customer management system built with Laravel 12 and Supabase integration. This application provides user registration, authentication, profile management, and an admin panel for managing customer data.

## Features

- **User Registration & Authentication**: Secure user registration with validation
- **Profile Management**: Users can edit their personal information
- **Admin Panel**: Complete admin interface for managing all users
- **Supabase Integration**: PostgreSQL database hosted on Supabase
- **Full Validation**: Comprehensive form validation for all user inputs
- **Responsive Design**: Modern UI with Tailwind CSS

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js and NPM
- Supabase account

## Installation

1. **Clone the repository**
   ```bash
   git clone <your-repository-url>
   cd EmbroideryFinal
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**
   ```bash
   npm install
   ```

4. **Configure environment variables**
   Copy `.env.example` to `.env` and update the following:

   ```env
   # Database Configuration (Supabase)
   DB_CONNECTION=pgsql
   DB_HOST=your-supabase-host.supabase.co
   DB_PORT=5432
   DB_DATABASE=postgres
   DB_USERNAME=your-username
   DB_PASSWORD=your-password

   # Supabase Configuration
   SUPABASE_URL=https://your-project.supabase.co
   SUPABASE_ANON_KEY=your-anon-key
   SUPABASE_SERVICE_KEY=your-service-key
   ```

5. **Generate application key**
   ```bash
   php artisan key:generate
   ```

6. **Run database migrations**
   ```bash
   php artisan migrate
   ```

7. **Build assets**
   ```bash
   npm run build
   ```

## Usage

### Starting the Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

### User Features

1. **Registration**: Users can register with name, email, phone, and password
2. **Login**: Secure authentication system
3. **Dashboard**: Personal dashboard showing user information
4. **Profile Management**: Edit personal information including password changes

### Admin Features

1. **Admin Dashboard**: Overview of system statistics and recent users
2. **User Management**: View, search, edit, and delete users
3. **User Creation**: Create new users through the admin panel

## Supabase Setup

To connect this application to Supabase:

1. Create a new project on [Supabase](https://supabase.com)
2. Go to Settings > Database and note your connection details
3. Update your `.env` file with the Supabase connection details
4. Run the migrations: `php artisan migrate`

## Security Features

- CSRF protection on all forms
- Password hashing using Laravel's built-in Hash facade
- Input validation and sanitization
- Route protection with authentication middleware
- Unique email and phone number validation

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
