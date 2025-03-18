# Order Processing API

A Laravel API that calculates the final payable order amount by applying dynamic taxes, discounts, and delivery fees.

## Features

- **Tax Calculation:**
  - 5% for orders $100–$500
  - 8% for orders $500–$1000
  - 12% for orders $1000+
- **Discounts:**
  - 5% off for orders over $200
  - Product combo discount applied if at least one discount product (e.g., "Laptop" or "Headphones") is present
  - Driven by database via the `Discount` model
- **Delivery Fee Calculation:**
  - Uses Haversine formula to calculate distance from warehouse to destination
  - Fee = base fee + (distance × cost per km) + weight-based surcharge (if weight > 5kg)
  - Managed via the `Delivery` model

## Setup

- Clone repository and run `composer install`
- Copy `.env.example` to `.env` and set your environment variables
- Run `php artisan key:generate`
- Migrate and seed database:
  - `php artisan migrate`
  - `php artisan db:seed` (includes discount and delivery fee rules)

## Usage

- **Endpoint:** `POST /api/order/calculate`
- **Payload Example:**

  ```json
  {
    "subtotal": 250.00,
    "weight": 10,
    "destination_latitude": 40.730610,
    "destination_longitude": -73.935242,
    "products": ["Laptop", "Headphones"]
  }
  ```

- **Response Includes:**
  - Subtotal, tax rate, tax amount
  - Discount rate and discount amount
  - Delivery fee and calculated delivery distance
  - Final payable amount
