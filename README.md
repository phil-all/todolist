![Library logo](documentation/readme-img/gitlab.jpg)

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/7acc790b38794896af831b263fe2535e)](https://www.codacy.com/gl/phil-all/todolist/dashboard?utm_source=gitlab.com&utm_medium=referral&utm_content=phil-all/todolist&utm_campaign=Badge_Grade)   ![coverage report](https://img.shields.io/badge/coverage-100%25-brightgreen)   [![pipeline status](https://gitlab.com/phil-all/todolist/badges/main/pipeline.svg)](https://gitlab.com/phil-all/todolist/-/commits/main)

* * *

## Table of contents

[:tada: Getting started](#getting-started)

-   [Prerequisites](#prerequisites)
-   [Installation](#installation)

[:wrench: Configuration](#configuration)

-   [Development environment](#development-environment)
-   [Test environment for docker bash use](#test-environment-for-docker-bash-use)
-   [Test environment for IDE use](#test-environment-for-ide-use)
-   [Test datas transaction and rollback](#test-datas-transaction-and-rollback)

[:white_check_mark: Tests bash custom commands](#tests-bash-custom-commands)

[:bookmark: Actual Release](#actual-release)

-   [Build with](#build-with)
-   [Bug fixes](#bug-fixes)
-   [New features](#new-features)

[:heavy_plus_sign: Third party dependencies and bundles](#third-party-dependencies-and-bundles)

[:whale: Docker stack](#docker-stack)

* * *

## :tada: Getting started

### Prerequisites

To be installed, ans used, this project requires:

-   git
-   composer
-   docker-compose

### Installation

First, clone project repository, and then install packages.

```bash
git clone git@gitlab.com:phil-all/todolist.git todolist
```

Install packages, and answer `no` to all recipes.

```bash
composer install
```

Due to doctrine deprecation, update twice composer to fix it.

```bash
composer update && \
composer update
```

Optimize autoloading

```bash
composer dump-autoload --optimize
```

Create your own development environment file `.env.local`, with docker database settings:

```code
DATABASE_URL="mysql://user:pass@db:3306/tododb?serverVersion=8.0"
```

Build and start **dockerized environment**.

```bash
composer docker
```

Launch the dockerized **development and testing bash**.

```bash
composer bash
```

Ensure `permissions` well setted.

```bash
composer chown
```

Then, create development database, create test database and load the fixtures in each of its, from development bash:

```bash
composer setdb && \
composer setdb-test
```

* * *

## :wrench: Configuration

### Development environment

Set development environment variables in a `.env.local` file, it would override `.env` file if needed.

Set test environment variables in a `.env.test.local`.

### Test environment for docker bash use

```code
DATABASE_URL="mysql://user:pass@db:3306/tododb_test?serverVersion=8.0"
```

### Test environment for IDE use

```code
DATABASE_URL="mysql://user:pass@127.0.0.1:3306/tododb_test?serverVersion=8.0"
```

### Test datas transaction and rollback

To ensure each test to be isolated regarding database actions, and be performed as many times as necessary, without other test side effect, be sure `dama/doctrine-test-bundle` is well configured.

```yaml
# ./config/packages/test/dama_doctrine_test
dama_doctrine_test:
    enable_static_connection: true
    enable_static_meta_data_cache: true
    enable_static_query_cache: true
```

```xml
<!-- ./phpunit.xml.dist -->

...
    <extensions>
        <extension class="DAMA\DoctrineTestBundle\PHPUnit\PHPUnitExtension" />
    </extensions>
</phpunit>
```

### Demo users

| username | password |
| -------- | -------- |
| admin_1  | pass1234 |
| user_2   | pass1234 |

* * *

## :white_check_mark: Tests bash custom commands

-   Make tests with debug option:

```bash
composer test
```

-   Make tests with debug option and generate code coverage report (consult it in your web browser: `./documentation/code-coverage/index.html`):

```bash
composer test--coverage
```

-   Make tests with testdox option:

```bash
composer test--dox
```

* * *

## :bookmark: Actual release

This project **improve a legacy** existing one ([todolist](https://github.com/saro0h/projet8-TodoList) by Saro0h), originaly made with symfony 3.1 and php 5.6.

### Build with

-   symfony 5.4
-   php 8.1 
    (via a docker environment: details in [docker stack](#docker-stack) section).

### Bug fixes

-   User have to be set in new tasks, and not been updated later.
-   Anonymous user is set in old existing tasks.
-   A role have to be set in user on creation: ROLE_USER or ROLE_ADMIN, and can be updated later.

### New features

-   Authorisation:
    -   User can delete only his own tasks.
    -   Only users with admin role can access to users management pages.
    -   Only users with admin role can delete nonymous tasks.
    -   Simple user can access only own tasks
    -   Admin access all task
    -   Only admin can create a user
-   Tests.

* * *

## :heavy_plus_sign: Third party dependencies and bundles

**Code quality**

-   phpro/grumphp with:
    -   phpstan
    -   phpcs
    -   phpmd

**Test**

-   phpunit
-   dama/doctrine-test-bundle

* * *

## :whale: Docker stack

| service    | version | ports     |
| :--------- | :------ | :-------- |
| nginx      | 1.11.10 | 8802:80   |
| php        | 8.1     |           |
| mysql      | 8.0.0   | 3306:3306 |
| phpmyadmin | latest  | 8080:80   |
| Blackfire  | 2       | 6379:6379 |
