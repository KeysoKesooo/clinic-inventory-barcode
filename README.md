# Inventory System with Barcode Scanning

A PHP + MySQL based Inventory Management System.  
This system allows scanning barcodes to manage stock in and stock out operations.

## Features

- Scan product barcode to retrieve product information
- Deduct stock quantity manually after scanning
- Show product photo and details upon scan
- Confirmation and cancel buttons for safe stock updates
- Inventory listing with quantities

## Requirements

- PHP 8.x
- MySQL / MariaDB
- Web server (Apache/Nginx or PHP built-in server)

## Setup

1. Clone this repo:
   ```bash
   git clone https://github.com/<username>/<repo>.git
   cd <repo>
   ```

CREATE DATABASE inventory_system;
USE inventory_system;
SOURCE database.sql;

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "inventory_system";
