# Ganesh Idol Booking System

A web-based application for browsing Ganesh idols, checking prices and availability, and booking idols online.

## Features

- Browse Ganesh idols with their prices and available stock
- Search and filter idols using the React interface
- Sort idols by price
- View idol details from the database
- Book idols through a PHP booking page
- Validate booking form inputs
- Manage idol records through a REST API
- Store idol information in MySQL

## Technologies Used

- **Frontend:** HTML, CSS, JavaScript, React
- **Backend:** PHP, Node.js, Express.js
- **Database:** MySQL
- **Development Environment:** XAMPP
- **React Development Server:** Vite

## Project Structure

```text
Ganesh-Idol-Booking/
├── api_idols.php
├── book.php
├── db.php
├── index.php
├── .gitignore
├── react-module/
│   ├── public/
│   └── src/
└── node-module/
    └── server.js
```

*Note: The structure above illustrates the main project components. Actual files may vary.*

## Requirements

- Windows
- XAMPP with Apache and MySQL
- Node.js and npm
- A web browser

## Setup Instructions

### 1. Start XAMPP

Start Apache and MySQL from the XAMPP Control Panel.

### 2. Configure the Database

1. Open phpMyAdmin at `http://localhost/phpmyadmin/`.
2. Create the database `ganesh_booking_db`.
3. Create the `idols` table with the columns required by the application.
4. Configure the database connection in `db.php`.

### 3. Run the PHP Application

Place the project in the XAMPP `htdocs` directory and open:

`http://localhost/ganesh-idol-booking/`

### 4. Run the Node.js API

Open PowerShell in the `node-module` directory:

```bash
npm install
node server.js
```

The API is expected to run at `http://localhost:3001` if configured with that port.

### 5. Run the React Application

Open another terminal in the `react-module` directory:

```bash
npm install
npm run dev
```

Open the Vite URL displayed in the terminal, usually `http://localhost:5173/`.

## API Overview

The Node.js API supports operations for retrieving and managing idol records, including:

- GET: Retrieve idol records
- POST: Add an idol
- PUT: Update an idol
- DELETE: Delete an idol

Check `node-module/server.js` for the exact routes and request formats.

## Database

The application uses MySQL to store Ganesh idol details, including name, price, and stock availability.

## Future Improvements

- Online payment integration
- Booking confirmation and receipt generation
- Admin dashboard
- Booking history
- Responsive UI improvements
- Deployment to a public hosting platform

## Author

**Sanket Shinde**

## License

This project is available for educational and learning purposes.
