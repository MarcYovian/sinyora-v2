# Comprehensive Dynamic Content Management Plan

This document outlines the plan to implement a comprehensive dynamic content management system (CMS) for all guest pages (Home, Events, Articles, Borrowing Assets). This will allow administrators to change content across various sections of these pages directly from the admin panel without modifying the source code.

## 1. Database Schema

A flexible and scalable database schema is required to handle content for multiple pages and sections.

### `content_settings` Table

This single table will store content for all pages in a key-value format, grouped by page and section.

-   `id`: Primary Key
-   `page`: The page where the content belongs (e.g., 'home', 'events').
-   `section`: The section of the page (e.g., 'hero', 'welcome', 'jadwal-misa').
-   `key`: The identifier for the content piece (e.g., 'title', 'subtitle', 'image', 'content').
-   `value`: The actual content (text, a path to an image, etc.).
-   `type`: The type of content to help render the correct input field in the admin panel (e.g., 'text', 'textarea', 'image', 'rich-text').
-   `created_at`: Timestamp
-   `updated_at`: Timestamp

A migration will be created for this table.

### Mass Schedule Management

To manage the "jadwal-misa" (mass schedule), I will first investigate the existing `app/Models/Schedules.php` model. Based on the findings, I will either extend the existing functionality or build a new management module within the admin panel.

## 2. Backend Development

### Models

-   A new Eloquent model, `ContentSetting`, will be created to interact with the `content_settings` table.

### Content Service

-   A `ContentService` will be created to provide a simple and centralized way to retrieve content for the frontend. This service will cache the content to improve performance and have helper methods like `ContentService::get('home', 'hero', 'title')`.

### Admin Panel

-   **Livewire Components:** Instead of a single component, a series of Livewire components will be created for managing the content of each page (e.g., `Admin\Pages\Content\Home`, `Admin\Pages\Content\Events`). This will provide a more organized and user-friendly experience in the admin panel. Each component will:
    -   Group content by sections.
    -   Render the appropriate form fields based on the `type` column (e.g., text input, textarea, file upload).
    -   Handle the updating of content.
-   **Routes:** A new group of routes will be added under `/admin/content/{page}` to make the content editing pages accessible.
-   **Permissions:** A set of permissions will be created for managing content on each page (e.g., 'manage home content', 'manage events content').

## 3. Frontend Integration

The existing Livewire components for the guest pages will be refactored to use the `ContentService` to fetch and display the dynamic content.

-   **Example: `app/Livewire/Pages/Home/Index.php`**: This component will call the `ContentService` to get all the content for the 'home' page and pass it to the view.
-   **Example: `resources/views/livewire/pages/home/index.blade.php`**: This view will be updated to render the content fetched from the service (e.g., `$content['hero']['title']`).

## 4. Implementation Steps

1.  **Create Migration:** Generate and run the migration for the `content_settings` table.
2.  **Create Model:** Create the `ContentSetting` model.
3.  **Create Content Service:** Implement the `ContentService` with caching.
4.  **Create Admin Routes:** Add the new routes for the content management pages in the admin panel.
5.  **Create Admin Livewire Components:** Build the Livewire components for managing the content of each guest page.
6.  **Implement Frontend:** Refactor the guest page Livewire components and Blade views to use the `ContentService` and display the dynamic content.
7.  **Mass Schedule:** Investigate and implement the management interface for the mass schedule.
8.  **Testing:** Thoroughly test all aspects of the new CMS.

This revised plan provides a robust and scalable solution for managing all content on your guest pages.
