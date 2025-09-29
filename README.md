# Symfony App

A modern Symfony 7.3 web application for managing posts with user authentication and API integration.

## Features

- **Post Management**: Create, edit, delete, and view posts
- **User Authentication**: Secure login/logout system
- **API Integration**: Fetch posts from JSONPlaceholder API
- **Search & Pagination**: Search posts by title with paginated results
- **Responsive UI**: Bootstrap-styled interface with Stimulus controllers

## Requirements

- PHP 8.2 or higher
- MySQL/MariaDB
- Composer
- Node.js (for asset management)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd symfonyapp
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env .env.local
   ```
   Update database credentials in `.env.local`:
   ```
   DATABASE_URL="mysql://username:password@127.0.0.1:3306/symfony_app"
   ```

4. **Create database and run migrations**
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. **Load fixtures (optional)**
   ```bash
   php bin/console doctrine:fixtures:load
   ```

6. **Install assets**
   ```bash
   php bin/console importmap:install
   ```

## Usage

### Development Server
```bash
symfony server:start
```
or
```bash
php -S localhost:8000 -t public/
```

### Key Routes
- `/login` - User authentication
- `/posts` - View all posts with search and pagination
- `/posts/new` - Create new post
- `/posts/{id}/edit` - Edit existing post
- `/posts/save` - Import posts from JSONPlaceholder API
- `/profile` - User profile page

### Import External Posts
Visit `/posts/save` to fetch and save posts from the JSONPlaceholder API.

## Project Structure

```
src/
├── Controller/     # Application controllers
├── Entity/         # Doctrine entities (Post, User)
├── Form/           # Symfony forms
├── Repository/     # Database repositories
├── Security/       # Authentication logic
└── DataFixtures/   # Database fixtures

templates/          # Twig templates
assets/            # Frontend assets (CSS, JS)
config/            # Application configuration
migrations/        # Database migrations
```

## Technologies Used

- **Backend**: Symfony 7.3, Doctrine ORM, PHP 8.2+
- **Frontend**: Twig, Stimulus, Bootstrap CSS
- **Database**: MySQL/MariaDB
- **Tools**: Composer, Asset Mapper, KnpPaginatorBundle

## Development

### Running Tests
```bash
php bin/phpunit
```

### Clear Cache
```bash
php bin/console cache:clear
```

### Generate New Migration
```bash
php bin/console make:migration
```

## Docker Support

The project includes Docker configuration files:
- `compose.yaml` - Main Docker Compose configuration
- `compose.override.yaml` - Development overrides

To run with Docker:
```bash
docker-compose up -d
```

## License

This project is proprietary software.