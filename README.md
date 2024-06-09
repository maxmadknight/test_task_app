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
3. Set up your `.env` file
4. Use `docker-compose up -d` to start the application
5. Run `php artisan migrate --seed`

## Endpoints

- `GET /tasks` - Retrieve a list of tasks with filters and sorting
- `POST /tasks` - Create a new task
- `PUT /tasks/{id}` - Update a task
- `PATCH /tasks/{id}/complete` - Mark a task as complete
- `DELETE /tasks/{id}` - Delete a task

## Usage

- Tasks can be filtered by status, priority, and searched by title and description.
- Tasks can be sorted by `created_at`, `completed_at`, and `priority`.

## License

This project is licensed under the MIT License.
