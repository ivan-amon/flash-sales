# Project Status 📊

Race conditions have been resolved using Redis-based locking. The payment gateway is simulated.

# Flash Sales ⚡

Flash Sales is a system built on a Laravel 12 (PHP 8.5) REST API, designed to manage events, tickets, and purchase orders for high-demand ticket sales and fast sales campaigns. It is prepared to resolve race conditions and preserve inventory integrity under heavy concurrent load using Redis-based atomic locking.

## Description 📝

It allows organizers to create and manage events and tickets, while users can browse events, reserve and purchase tickets, and manage their orders. The system supports different statuses for orders and tickets, as well as sale start dates and order expiration. Payments are simulated through a payment gateway service.

The core of the system is the Laravel API, which can be consumed directly. A Vue 3 + TypeScript frontend is included as a reference client, but it is optional — you are free to consume the API however you like.

## Project Structure 🗂️

### Backend (Laravel API)

- **app/Models/**: Eloquent models for main entities (Event, Order, Ticket, User, Organizer) 🎟️
- **app/Http/Controllers/**: Controllers for business logic and API endpoints 🧠
- **app/Actions/**: Specific actions for events and orders ⚙️
- **app/Enums/**: Enums for order and ticket statuses 🔢
- **app/Services/**: Services such as simulated payment gateways 💳
- **database/migrations/**: Migrations for the database structure 🗄️
- **database/seeders/**: Seeders to populate sample data 🌱
- **database/factories/**: Factories for test data generation 🧪
- **routes/**: API, web, and console route definitions 🌐
- **tests/**: Unit and integration tests 🧪

### Frontend (Vue SPA)

- **frontend/src/views/**: Page-level components, including an `organizer/` area for event management 📄
- **frontend/src/components/**: Reusable UI components (filter bars, ticket modals, etc.) 🧩
- **frontend/src/composables/**: Reusable Vue composition functions 🔁
- **frontend/src/router/**: Vue Router route definitions 🧭
- **frontend/src/utils/**: Helpers such as the API client (`http.ts`) and date utilities 🛠️
- **frontend/src/types/**: TypeScript type definitions 📐

## Future Improvements 🚀

Realistic payment gateways will be integrated in future versions to support real-world payment processing.

## Installation 🛠️

### Backend

1. Clone the repository and enter the project directory.
2. Install Composer dependencies.
3. Copy the `.env.example` file to `.env` and configure your environment variables.
4. Generate the application key and run migrations and seeders.
5. Start the environment using Laravel Sail (`vendor/bin/sail up -d`). The API runs at `http://localhost:8000`.

### Frontend

1. Enter the `frontend/` directory.
2. Install Node.js dependencies with `npm install`.
3. Start the dev server with `npm run dev`, or build for production with `npm run build`.

The frontend expects the API at `http://localhost:8000/api` (configured in `frontend/src/utils/http.ts`).

See the internal documentation or code comments for more details about the structure and functionality.

---
Technologies used in this project include:

**Backend**
- Eloquent ORM 🎟️
- Sanctum (API authentication) 🔐
- Sail (development environment) 🐳
- Redis (race condition prevention via atomic locking) 🔒
- Laravel Boost (AI tooling) 🤖

**Frontend**
- Vue 3 + TypeScript ⚡
- Vue Router 🧭
- Vite (build tooling) 📦
- Bootstrap 🎨

Project developed with Laravel 12 and Vue 3. ❤️
