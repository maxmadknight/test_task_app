# Todo List API

This API allows you to manage a list of tasks with the following features:
- Create, edit, delete tasks
- Mark tasks as complete
- Filter and sort tasks
- Support for subtasks

## Requirements

- PHP 8.1+
- Laravel 8+
- Docker

## Installation

1. Clone the repository
2. Run `composer install`
3. Set up your `.env` (the docker data connection is already in the example file, so just `cp .env.example .env`, yes,
   in a real project I did not act like that)
4. Use `./vendor/bin/sail up -d` to start the application
5. Run `./vendor/bin/sail artisan migrate --seed` or if you use regular docker
   compose `docker-compose exec laravel.test sh` and run `php artisan migrate --seed`

## Endpoints

all except login and register endpoint are with auth check, auth by token `Authorization: Bearer %token%`

- `POST /register` - create new user
- `POST /login` - auth user
- `POST /logout` - logout user
- `GET /user` - get info about current auth user
- `GET /tasks` - Retrieve a list of tasks (top level only) with filters and sorting
- `POST /tasks` - Create a new task
- `GET /tasks/{id}` - Get specific task data with subtasks info
- `PUT /tasks/{id}` - Update a task
- `DELETE /tasks/{id}` - Delete a task
- `PATCH /tasks/{id}/complete` - Mark a task as complete

## License

This project is licensed under the MIT License.
