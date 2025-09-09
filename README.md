# XML Bulk Import for Contacts (Laravel)

A Laravel-based application providing:

- Standard CRUD operations for managing contacts.
- A high-performance bulk import feature via XML, optimized for large datasets using Laravel **queues**, **bus batches**, and **memory-efficient processing**.

---

## Table of Contents

- [Features](#features)  
- [Tech Stack](#tech-stack)  
- [Prerequisites](#prerequisites)  
- [Installation & Setup](#installation--setup)  
- [Running the Application](#running-the-application) 

---

## Features

- Full CRUD interface for contacts.
- Bulk import contacts via XML.
- Efficient handling with Laravel queues, bus batches, and chunked processing.
- Scalable for large datasets with minimal memory usage.

---

## Tech Stack

- **Language:** PHP 8.2 
- **Framework:** Laravel 12
- **Queue Drivers:** `database`
- **Dependencies:** Composer  
- **Environment Management:** `.env`, `artisan` commands  

---

## Prerequisites

Ensure your environment meets the following:

- PHP 8.2 with required extensions (e.g., XML, PDO)
- Composer
- A supported database (MySQL)
- Queue system (e.g., `database`) configured in `.env`

---

## Installation & Setup

```bash
# 1. Clone the repository
git clone https://github.com/Madhuri29M/xml-bulk-import.git
cd xml-bulk-import

# 2. Install dependencies
composer install

# 3. Set up environment
cp .env.example .env
php artisan key:generate

# 4. Configure your .env database and queue settings as needed

# 5. Run migrations and seed (if applicable)
php artisan migrate
php artisan db:seed

# 6. Start the queue worker (in a separate terminal)
php artisan queue:work
