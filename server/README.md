# GoSummarize: the back-end app

- Developed with _ApiPlatform 4_.
- PHP 8.5.
- PHPUnit.
- PHPStan.
- Docker (with compose).

## Setting up your dev environment

- Copy the _.env.dist_ file at the root of the folder to a _.env_ one and change what needs to be changed.
- Do the same thing with the _app/.env.dist_ file.
- Run ``Make install``.

Notes
- Fixtures are included. You can see them (including the default user credentials) in the _src/DataFixtures/AppFixtures.php_ file.
- There is and _Adminer_ container. You can connect to the _mysql_ host with the credentials defined in the _docker-compose.yaml_ file.
- Run ``Make help`` to see the various commands available.
- API documentation is available like any standard ApiPlatform project.
- See the _src/Console_ folder to see the various commands available. 
  - In production, some of them will be run on a daily basis using _Messenger_ and a scheduler.
    - Fetching the feeds.
    - Deleting outdated ApiTokens.
    - Deleting old pages.
  - The last one is used to create new users.


## Deploying into production

There is almost nothing specific: it just like deploying the standard Symfony application.
However, you need to define a recurring task (using _Supervisor_ is recommended) to run the schedule that will run all the asynchronous tasks: ``bin/console messenger:consume scheduler_default --env=prod --memory-limit=256M``

## Security

- The application is only accessible through the API.
- There is no admin interface.
- A user might have many API tokens.
- Each token is hashed.
- Each time you authenticate using the login endpoint, a new token is created.
