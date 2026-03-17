


# Flash Sales ⚡

Flash Sales is a web API built with Laravel 12 and PHP 8.5, designed to manage events, tickets, and purchase orders for fast sales campaigns.

## Description 📝

It allows organizers to create events, manage tickets, process orders, and simulate payments. The system supports different statuses for orders and tickets, as well as sale start dates and order expiration.

## Project Structure 🗂️

- **app/Models/**: Eloquent models for main entities (Event, Order, Ticket, User, Organizer) 🎟️
- **app/Http/Controllers/**: Controllers for business logic and API/web endpoints 🧠
- **app/Actions/**: Specific actions for events and orders ⚙️
- **app/Enums/**: Enums for order and ticket statuses 🔢
- **app/Services/**: Services such as simulated payment gateways 💳
- **database/migrations/**: Migrations for the database structure 🗄️
- **database/seeders/**: Seeders to populate sample data 🌱
- **database/factories/**: Factories for test data generation 🧪
- **routes/**: API, web, and console route definitions 🌐
- **tests/**: Unit and integration tests 🧪


## Future Improvements 🚀

This project aims to handle concurrency issues such as race conditions in future releases, ensuring robust and reliable order and ticket management during high-demand flash sales.

## Installation 🛠️

1. Clone the repository and enter the project directory.
2. Install Composer and Node.js dependencies.
3. Copy the `.env.example` file to `.env` and configure your environment variables.
4. Generate the application key and run migrations and seeders.
5. Start the environment using Laravel Sail.

See the internal documentation or code comments for more details about the structure and functionality.

---
Project developed with Laravel 12. ❤️
