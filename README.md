# Inventory System with Barcode Scanning

A PHP + MySQL based Inventory Management System.  
This system allows scanning barcodes to manage stock in and stock out operations.

## Features

- Scan product barcode to retrieve product information
- Deduct stock quantity manually after scanning
- Show product photo and details upon scan
- Confirmation and cancel buttons for safe stock updates
- Inventory listing with quantities
- **Import users and products from CSV files**:
  - Users: import from CSV, skips duplicates based on full name or username, generates login credentials automatically
  - Products: import from CSV, skips duplicates based on product title, automatically handles product photo upload
- Export filtered products to CSV

## Requirements

- PHP 8.x
- MySQL / MariaDB
- Web server (Apache/Nginx or PHP built-in server)

## Setup

Clone this repo:

```bash
git clone https://github.com/<username>/<repo>.git
cd <repo>
```
