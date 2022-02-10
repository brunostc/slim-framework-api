# PHP Challenge
- The objective of this challenge is to create a REST API application that the user can use to track the value of stocks in the stock market. The base for your application as presented here uses a simple implementation of Slim Framework
## Project Requirements

- PHP 7.4+
- Composer 2+
- MySQL 5.8+
- A mailing server (I used [mailtrap](https://mailtrap.io/))
  
## Instalation Process
- Run `cp .env.sample .env` to copy the .env sample file and then edit it to add the mail server credentials.
- Edit the file `app/config/database.php` in order to add your database credentials.
- Then import the file `database_structure.sql` at you preferred DBMS, you'll need the tables to register and save your data.
- The second step is to install your dependencies, run `composer install` at the root of your project.
- Then run `cd public` and then `php -S localhost:8000` (I used port 8000 but that is up to you).
- Once you do all that your API is now running!

## Usage
- Use your preferred request software to test all endpoints (I used Postman, so I've my collection used for developing) and start making requests!
- Endpoints:
    - `POST /auth/register`
      - email: string
      - password: string
    - `POST /auth/login`
      - email: string
      - password: string
    - `GET /app/stocks?stock=YOUR_STOCK_HERE`
    - `GET /app/history`

## Testing
- Unfortunately I didn't have enough time to implement automated testing, but who know? Maybe I'll update the repo later.
 

### Author: [Bruno Stacheski](https://github.com/brunostc)