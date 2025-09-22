# Sinyora - Integrated Management System

Sinyora is a comprehensive web application built with the TALL stack (Tailwind CSS, Alpine.js, Laravel, Livewire) designed to streamline organizational management. It provides a suite of tools for handling events, documents, assets, and more, all within a modern, reactive user interface.

## Key Features

- **Event Management:** Schedule, manage, and track internal and external events. Includes features for recurrence, categorization, and approval workflows.
- **Document Management:** A complete lifecycle for documents, from proposal and creation to analysis and finalization. Supports various document types and signature handling.
- **Asset & Borrowing System:** Manage organizational assets, track their status, and handle borrowing requests with availability checks.
- **User & Organization Management:** Tools for managing users, groups, and organizational structures.
- **Dynamic Frontend:** Built with Livewire and Volt, offering a seamless and interactive user experience without leaving PHP.
- **Data-Driven:** Utilizes Data Transfer Objects (DTOs) for robust and structured data handling between application layers.

## Tech Stack

- **Backend:** Laravel
- **Frontend:** Livewire, Volt, Tailwind CSS, Alpine.js
- **Database:** (Configurable, e.g., MySQL, PostgreSQL)
- **Build Tool:** Vite

## Getting Started

Follow these instructions to get a local copy of the project up and running for development and testing purposes.

### Prerequisites

- PHP (>= 8.2)
- Composer
- Node.js & npm
- A database server (e.g., MySQL, MariaDB)

### Installation

1.  **Clone the repository:**
    ```sh
    git clone https://github.com/your-username/sinyora.git
    cd sinyora
    ```

2.  **Install PHP dependencies:**
    ```sh
    composer install
    ```

3.  **Install JavaScript dependencies:**
    ```sh
    npm install
    ```

4.  **Set up your environment:**
    - Copy the example environment file and configure it for your local setup (especially database credentials).
    ```sh
    cp .env.example .env
    ```
    - Open `.env` and set your `DB_*` variables.

5.  **Generate application key:**
    ```sh
    php artisan key:generate
    ```

6.  **Run database migrations:**
    ```sh
    php artisan migrate
    ```

7.  **(Optional) Seed the database with test data:**
    ```sh
    php artisan db:seed
    ```

## Usage

### Development Server

To run the application locally, start the Vite development server and the Laravel server.

1.  **Start the Vite server:**
    ```sh
    npm run dev
    ```

2.  **In a separate terminal, start the Laravel server:**
    ```sh
    php artisan serve
    ```

The application will be available at `http://localhost:8000`.

### Building for Production

To compile and minify frontend assets for production, run:
```sh
npm run build
```

## Running Tests

To run the PHPUnit test suite, execute the following command:
```sh
php artisan test
```

## Contributing

Contributions are what make the open-source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue with the tag "enhancement".

1.  Fork the Project
2.  Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3.  Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4.  Push to the Branch (`git push origin feature/AmazingFeature`)
5.  Open a Pull Request

## License

This project is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).