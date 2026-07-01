# Flash Sales ⚡

Flash Sales is a system built on a Laravel 12 (PHP 8.5) REST API, designed to manage events, tickets, and purchase orders for high-demand ticket sales and fast sales campaigns. It is prepared to resolve race conditions and preserve inventory integrity under heavy concurrent load using Redis-based atomic locking.

## Description

It allows organizers to create and manage events and tickets, while users can browse events, reserve and purchase tickets, and manage their orders. The system supports different statuses for orders and tickets, as well as sale start dates and order expiration. Payments are simulated through a payment gateway service.

The core of the system is the Laravel API, which can be consumed directly. A Vue 3 + TypeScript frontend is included as a reference client, but it is optional — you are free to consume the API however you like.

## Installation

### Backend

1. Clone the repository and enter the project directory.
2. Install Composer dependencies.
3. Copy the `.env.example` file to `.env` and configure your environment variables.
4. Generate the application key and run migrations and seeders.
5. Start the whole environment using Laravel Sail (`vendor/bin/sail up -d`). The API runs at `http://localhost:8000`.

### Frontend

The Vue 3 frontend is fully Dockerized as its own Sail service, so there's **no need to run `npm install` or `npm run dev` manually** — `vendor/bin/sail up -d` builds the container, installs dependencies, and starts the Vite dev server automatically.

- The frontend is served at `http://localhost:5173` (override with the `FRONTEND_PORT` environment variable).
- It points to the API via the `VITE_API_BASE_URL` environment variable, which defaults to `http://localhost:8000/api`.

See the internal documentation or code comments for more details about the structure and functionality.

---
Technologies used in this project include:

**Backend**
- Eloquent ORM
- Sanctum (API authentication)
- Sail (development environment)
- Redis (race condition prevention via atomic locking)

**Frontend**
- Vue 3 + TypeScript
- Vue Router
- Vite (build tooling)
- Bootstrap

Project developed with Laravel 12 and Vue 3. ❤️
