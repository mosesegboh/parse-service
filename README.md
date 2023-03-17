# parse-service
A parse-service built with symfony and rabbitmq for queuing in parallel processes.

This is a scrape service built with symfony 5.4
To Run
- clone fork into local env.
- cd into dir.
- run composer install to install dependencies.
- run mv env.example .env to set up your env file.
- replace .env DATABASE_URL and MESSENGER_TRANSPORT_DSN with yours (I will leave mine in the example for easy set up).
- run docker-compose up -d to pull in necessary images.
- symfony open:local:rabbitmq to access rabbit mq broker dashboard
- run command symfony console app:get-news to start scraping process.
- remember to run symfony console messenger:consume async -vv for the process to be consumed.
