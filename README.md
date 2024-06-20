# RENT ID API PROJECT
## Description
This project is a REST API that allows you to manage the rent of a property. It allows you to create, read, update and delete properties, tenants and rents. It also allows you to filter properties by city and to filter tenants by property. The project is developed in Laravel 11.
## Installation
1. Clone the repository
```bash
git clone https://github.com/Arg39/kelompok-3-finalproject-backend.git
```
2. Install the dependencies
```bash
composer install
```
3. Create a new database
```bash
Database name: rentid
```
4. Copy the .env.example file and rename it to .env
```bash
cp .env.example .env
```
5. Generate the application key
```bash
php artisan key:generate
```
6. Run the migrations with seed
```bash
php artisan migrate --seed
```
7. Run jwt secret key
```bash
php artisan jwt:secret
```
8. Start the server
```bash
php artisan serve
```
## API Documentation
The API documentation can be found [here](https://www.apidog.com/apidoc/shared-67a7ce07-2c49-440b-8381-af343e25b68f)
## Team Project
- [Hans Andi Wijaya](https://github.com/HansAndi)
- [Nasyath Faykar](https://github.com/faykarr)
- [Alfian Pabannu](https://github.com/Arg39)