# EcoRide - Carpooling Application
# 🏎️ ÉcoRide - Green Carpooling Platform

## Project Description
This repository contains the source code and infrastructure configuration for the **ÉcoRide** (personal) project.

## 🌐 Live Demo
The application is currently deployed and maintained on a personal infrastructure.  
👉 **[Access the Live Application](https://www.ecoride-pro.uk/)**


## 🛠️ Infrastructure & DevOps Focus
The core value of this repository lies in the deployment architecture designed to ensure service security and availability.

### 🐳 Containerization (Docker)
The application is decoupled into isolated services using **Docker-Compose** for granular resource management:
* **Web Server & PHP-FPM** (Symfony).
* **Database** (MariaDB).

### 🛡️ Deployment & Security (Debian)
The infrastructure is hosted on a dedicated **Debian** server. The production setup leverages modern technologies for secure traffic handling:
* **Cloudflare Tunnel**: Exposes the application without opening inbound ports on the local router, protecting the host from direct attacks.
* **TLS/SSL Termination**: End-to-end encryption between the user and the server.
* **DNS Management & Reverse Proxy**: Optimized request routing to the respective containers.

---
