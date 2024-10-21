# Canoe technical test (Laravel Fund management app)

Welcome to the Canoe technical test! This guide will walk you through the steps required to set up the project locally using Docker, run it, execute tests, and explore the available API routes. You’ll also find instructions for using a pre-built Postman collection to test the API endpoints.

## Table of Contents

1. [Prerequisites](#Prerequisites)
2. [Installation](#installation)
3. [Usage](#usage)
4. [Running Tests](#running-tests)
5. [Database Schema and ERD](#database-schema-and-erd)


### Prerequisites

Make sure you have the following installed on your machine:

- [Docker](https://www.docker.com/products/docker-desktop)
- [Docker Compose](https://docs.docker.com/compose/install/)
- [Postman](https://www.postman.com/downloads/)


## Installation

This project is fully Dockerize, meaning you don't need to install PHP, MySQL, or Redis directly on your machine. Instead, everything will run inside Docker containers.


### Steps to Set Up the Application

#### 1. **Clone the Repository**

```bash
git clone https://github.com/saasscaleup/canoe-app.git
cd canoe-app
```


#### 2.	Build and Start the Containers
Build the Docker containers and start them in detached mode.
```bash
docker-compose up -d --build
```

#### 3.	Run migration with seed
   Run the database migrations to set up the required tables. This step ensures that the database schema is in place.

```bash
docker exec laravel_app /bin/sh -c "php artisan migrate --seed --force"
```

### Access the Application

To access the application, visit:

http://localhost:8000

You can now interact with the API via your browser or tools like Postman.

## Usage

### API Routes

This application provides a set of RESTful API routes that allow you to interact with the application data. Below is a summary of the key routes available.

#### API Endpoints

| Method | Endpoint                  | Description                              |
|--------|---------------------------|------------------------------------------|
| GET    | `/api/v1/funds`            | List all funds (supports filtering)     |
| POST   | `/api/v1/funds`            | Create a new fund                       |
| GET    | `/api/v1/funds/{id}`       | Retrieve details of a specific fund     |
| PUT    | `/api/v1/funds/{id}`       | Update a specific fund                  |
| DELETE | `/api/v1/funds/{id}`       | Delete a specific fund                  |


#### Filtering Options (GET `/api/v1/funds`)

You can filter the list of funds by providing the following query parameters:

- `name`: Filter funds by name or alias(string).
- `fund_manager`: Filter funds by the name of the fund manager(int).
- `start_year`: Filter funds by their starting year(year).

#### Example Request:

```
GET /api/v1/funds?name=example&start_year=2020
```

This will return all funds where the name contains "example" and the starting year is 2020.


#### Using the Postman Collection

To make testing easier, we've created a Postman collection that includes all the available API routes with pre-configured examples.

##### Steps to Use the Postman Collection:

1. **Download the Collection**: 
   [Postman Collection Download Link](https://github.com/saasscaleup/canoe-app/blob/master/canoe.postman_collection.json?raw=true) (Link to your collection file)

2. **Import the Collection**: 
   Open Postman, click on "Import", and select the downloaded `.json` file.

3. **Set Environment Variables**:
   After importing, make sure to set the `canoe_base_url` variable to point to `http://localhost:8000`.

4. **Run the API Requests**: 
   You can now run the pre-configured requests to test the API endpoints directly from Postman.


## Running Tests

You can run the automated tests for this application within the Docker container.

```bash
docker exec laravel_app /bin/sh -c "php artisan test"
```

## ERD Diagram


![ERD Diagram Placeholder](https://github.com/saasscaleup/canoe-app/blob/master/canoe-erd.png?raw=true)


---

If you encounter any issues, feel free to open a GitHub issue or reach out via email.

Thank you for checking out the project!

---

### Notes

- **Queue Workers**: If you're setting up queues, make sure the Redis service is running, and use the queue worker to process jobs.
- **Caching**: Laravel's cache driver is set to use Redis. Ensure Redis is running for cache functionality.

## Suggestions and Best Practices to support Scaling and Security

### 1. Use UUID as id or uid

Consider using UUIDs as another identifier (uid or UUID) UUIDs provide globally unique identifiers, improving security and scalability, especially in distributed systems. **(Was not applied for this task)**

### 2. Use Cache for API Responses 

Implement caching for API responses, particularly for data that isn’t frequently updated. Use the Last-Modified header to ensure clients only re-fetch data when necessary, improving performance and reducing database load.

```php
GET /api/v1/funds -> is cached in order to take scaling in consideration **(Was applied for this task ✅)**

public function getFundsCache(FilterFundRequest $request): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        // Define a cache key.
        $cacheKey = 'funds_index_' . md5(serialize($request->all()));

        // can be defined also globally
        $cacheDuration = 60;

        $funds = Cache::tags(['funds'])->remember($cacheKey, $cacheDuration, function () use ($request) {
            return Fund::with(['fundManager', 'fundAliases', 'companies'])
                ->filterByName($request->name)
                ->filterByFundManager($request->fund_manager)
                ->filterByStartYear($request->start_year)
                ->paginate($request->get('per_page', 10));
        });

        return $funds;
    }
```

GET /api/v1/funds/{fund_id} -> can also be cached to improve performance! **(Was not applied for this task)**

### 3. Use API Throttling to Prevent Overload

We can apply rate limiting to our API using Laravel’s throttle middleware to prevent excessive requests and protect the server from being overloaded. **(Was not applied for this task)**

### 4. Database Indexing

As our dataset grows, query performance can degrade. In order to ensure that we implement proper indexing on frequently queried columns. This will significantly speed up read operations and prevent performance bottlenecks. **(Was applied for this task ✅)**

```
 Schema::table('funds', function (Blueprint $table) {
            $table->index('name'); 
            $table->index('fund_manager_id'); 
            $table->index('start_year');
        });

        Schema::table('fund_aliases', function (Blueprint $table) {
            $table->index('name'); 
        });
```

We can also consider creating Compound Index in case we know what are the columns that get queering the most **(Was not applied for this task)**

### 5. Pagination for Large Data Sets

When dealing with large datasets, it's recommend to paginate our API responses to avoid overwhelming the client and the server. This reduces the amount of data loaded at once and helps balance memory usage and response times. **(Was applied for this task ✅)**

```php
return Fund::with(['fundManager', 'fundAliases', 'companies'])
        ->filterByName($request->name)
        ->filterByFundManager($request->fund_manager)
        ->filterByStartYear($request->start_year)
        ->paginate($request->get('per_page', 10));
```

### 6. Queue Background Jobs

Offload long-running processes, such as sending emails or processing large datasets, to a queue system (I'm using Redis as queue driver). This ensures that the main application remains responsive even under heavy load. **(Was applied for this task ✅)**

```php
<?php

namespace App\Listeners;

use App\Events\DuplicateFundWarning;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleDuplicateFundWarning implements ShouldQueue
{
...
```

### Other scaling suggestion

1. Modify `php.ini` to fit servers resources
2. Modify `nginx.conf` to fit servers resources
3. Use Load balancer with autoscaling group (AWS)