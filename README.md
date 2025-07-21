# EcoRide - Carpooling Application

## Project Description

**EcoRide** is a carpooling application developed as part of a school project. It aims to facilitate connections between drivers and passengers for shared journeys, promoting more ecological and economical mobility.

## Key Features

- Search for trips by departure location, destination, and date.
- Offer trips (for drivers).
- User profile management (login/registration).
- Review and rating system.

## Technologies Used

- **Backend**: Symfony 6.4 (PHP 8.3)
- **Database**: MySQL 8.0
- **Frontend**: Vanilla JavaScript, HTML5, CSS3 (SCSS), Bootstrap 5
- **Containerization**: Docker, Docker Compose

## Prerequisites

Make sure you have the following installed on your machine:

- **Docker Desktop (or Docker Engine)**  
  👉 [Download Docker Desktop](https://www.docker.com/products/docker-desktop/)

- **Visual Studio Code**  
  👉 [Download VS Code](https://code.visualstudio.com/)

- **VS Code "PHP Server" Extension**  
  👉 Install it directly from the VS Code Extensions Marketplace.

- **Node.js and npm**  
  👉 [Download Node.js](https://nodejs.org/)

## Installation and Launching the Application

### 1. Clone the Repository and Switch to `developement` Branch

```bash
git clone https://github.com/Flo466/EcoRide.git
cd EcoRide
git checkout development
```
---

### 2. Backend Setup and Launch

Navigate to the backend directory:

```bash
cd backend
```

Create a file named `.env` with the following content:

```env
APP_ENV=dev
APP_SECRET=your_symfony_secret_here (generate a key)

DATABASE_URL="mysql://${MYSQL_USER}:${MYSQL_PASSWORD}@ecoride_db:3306/${MYSQL_DATABASE}?serverVersion=8.0.32&charset=utf8mb4"

MYSQL_ROOT_PASSWORD=root_password_ecoride (use you own password)
MYSQL_DATABASE=ecoride
MYSQL_USER=ecoride_user (use you own user name)
MYSQL_PASSWORD=ecoride_password (use you own password)

CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'
```

You can generate a secure random string for APP_SECRET using the following command:

```bash
php -r "echo bin2hex(random_bytes(32));"

```

Then launch the services:

```bash
docker-compose up --build -d
```

To verify backend readiness:

```bash
docker-compose logs symfony_backend
```

Look for:  
**`Development server listening on http://0.0.0.0:8000`**

---

### 3. Frontend Launch

The frontend is located in the root directory of the project EcoRide.
Run the following command to install all necessary dependencies:

```bash
npm install
```

Launch Live Server from VS Code:

- At the root of the project
- Click left `index.html`, then click the **"PHP Server"** icon in the top-right bar

  ![alt text](assets\images\image-1.png)

---

### 4. Access the Application

- **Frontend**: `http://localhost:3000`

The frontend will automatically communicate with the backend.

---

🎉 Your **EcoRide** application is now up and running locally!
