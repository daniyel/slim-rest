# Slim 3 Rest API Prototype

Just a spare time project, for getting the rust off. This is in no way a production ready example.

You can use and get "inspired" by this project :D.

This project is as it is.

## Important!!!

There is default admin user with username `foo.bar@example.org` and password `Admin12345`. This user should be removed as soon as you create your own admin user.

Use `DELETE FROM users WHERE id = 1;`.

## Dependencies

 - lcobucci/jwt
 - sodium (install via PECL)
 - uses Sqlite 3

# Development

1. Init database

```
$ sqlite3 SlimRest.sqlite3 < init.sql
```

2. Install dependencies

```
$ composer install [--no-dev]
```

3. Start development server

```
$ composer start
```

## Re-generate autoload dependencies

If you added or installed new dependencies you should run this command

```
$ composer dump-autoload
```

## Run tests

```
$ composer test
```

# API documentation

## API Resources

- [POST /products](#post-products)
- [GET /products/[id]](#get-productsid)
- [POST /bundles](#post-bundles)
- [GET /bundles/[id]](#get-bundlesid)
- [GET /bundles/[id]/products](#get-bundlesidproducts)
- [POST /orders](#post-orders)
- [GET /orders/[id]](#get-ordersid)
- [GET /orders/[id]/products](#get-ordersidproducts)
- [GET /orders/[id]/bundles](#get-ordersidbundles)
- [POST /roles](#post-roles)
- [GET /roles/[id]](#get-rolesid)
- [GET /users/[id]](#get-usersid)
- [GET /users/[id]/roles](#get-usersidroles)
- [POST /users/[id]/roles/[id]](#post-usersidrolesid)
- [POST /auth/register](#post-authregister)
- [POST /auth/login](#post-authlogin)

### POST /products

Example: http://localhost:8080/products

Request headers:
    - Content-Type: application/json
    - Authorization: Bearer <jwt_token>
Request body:

    {
        "name": "Product3",
        "price": "150.00",
        "discount": "15.00",
        "discountType": "variable"
    }

Response body: None
Response status:
    - Success: 201
    - Error: 400
Response headers: Location: /products/[id]

### GET /products/[id]

Example: http://localhost:8080/products/[id]

Request headers:
    - Authorization: Bearer <jwt_token>

Response body:

    {
        "id": "3",
        "name": "Product3",
        "price": "150.00",
        "discount": "15.00",
        "discountType": "variable"
    }

Response status:
    - Success: 200
    - Error: 400

### POST /bundles

Example: http://localhost:8080/bundles

Request headers:
    - Content-Type: application/json
    - Authorization: Bearer <jwt_token>
Request body:

    {
        "name": "Bundle1",
        "price": "100.00",
        "products": [2,3]
    }


Response body: None
Response status:
    - Success: 201
    - Error: 400
Response headers: Location: /bundles/[id]

### GET /bundles/[id]

Example: http://localhost:8080/bundles/[id]

Request headers:
    - Authorization: Bearer <jwt_token>

Response body:

    {
        "id": "1",
        "name": "Bundle1",
        "price": "100.00"
    }

Response status:
    - Success: 200
    - Error: 400

### GET /bundles/[id]/products

Example: http://localhost:8080/bundles/[id]/products

Request headers:
    - Authorization: Bearer <jwt_token>

Response body:

    {
        "id": "1",
        "name": "Bundle1",
        "price": "100.00"
    }

Response status:
    - Success: 200
    - Error: 400

### POST /orders

Example: http://localhost:8080/orders

Request headers:
    - Content-Type: application/json
    - Authorization: Bearer <jwt_token>
Request body:

    {
        "totalPrice": "100.00",
        "products": [2,3],
        "bundles": [1]
    }


Response body: None
Response status:
    - Success: 201
    - Error: 400
Response headers: Location: /orders/[id]

### GET /orders/[id]

Example: http://localhost:8080/orders/[id]

Request headers:
    - Authorization: Bearer <jwt_token>

Response body:

    {
        "id": "4",
        "totalPrice": "430.00"
    }

Response status:
    - Success: 200
    - Error: 400

### GET /orders/[id]/products

Example: http://localhost:8080/orders/[id]/products

Request headers:
    - Authorization: Bearer <jwt_token>

Response body:

    [
        {
            "id": "1",
            "name": "Product1"
        },
        {
            "id": "2",
            "name": "Product2"
        },
        {
            "id": "3",
            "name": "Product3"
        }
    ]

Response status:
    - Success: 200
    - Error: 400

### GET /orders/[id]/bundles

Example: http://localhost:8080/orders/[id]/bundles

Request headers:
    - Authorization: Bearer <jwt_token>

Response body:

    [
        {
            "id": "1",
            "name": "Bundle1"
        }
    ]

Response status:
    - Success: 200
    - Error: 400

### POST /roles

Example: http://localhost:8080/roles

Request headers:
    - Content-Type: application/json
    - Authorization: Bearer <jwt_token>
Request body:

    {
        "role": "admin"
    }


Response body: None
Response status:
    - Success: 201
    - Error: 400
Response headers: Location: /roles/[id]

### GET /roles/[id]

Example: http://localhost:8080/roles/[id]

Request headers:
    - Authorization: Bearer <jwt_token>

Response body:

    {
        "id": "1",
        "role": "admin"
    }

Response status:
    - Success: 200
    - Error: 400

### GET /users/[id]

Example: http://localhost:8080/users/[id]

Request headers:
    - Authorization: Bearer <jwt_token>

Response body:

    {
        "id": "1",
        "username": "foo.bar@example.org"
    }

Response status:
    - Success: 200
    - Error: 400

### GET /users/[id]/roles

Example: http://localhost:8080/users/[id]/roles

Request headers:
    - Authorization: Bearer <jwt_token>

Response body:

    [
        {
            "id": "1",
            "role": "Role1"
        },
        {
            "id": "2",
            "role": "Role2"
        }
    ]

Response status:
    - Success: 200
    - Error: 400

### POST /users/[id]/roles/[id]

Example: http://localhost:8080/users/3/roles/1

Request headers:
    - Content-Type: application/json
    - Authorization: Bearer <jwt_token>
Request body: None

Response body: None
Response status:
    - Success: 200
    - Error: 400

### DELETE /users/[id]

Example: http://localhost:8080/users/3

Request headers:
    - Content-Type: application/json
    - Authorization: Bearer <jwt_token>
Request body: None

Response body: None
Response status:
    - Success: 200
    - Error: 400

### POST /auth/register

Example: http://localhost:8080/auth/register

Request headers:
    - Content-Type: application/json
Request body:

    {
        "username": "foo.bar@example.org",
        "password": "MyPassword12345"
    }


Response body: None
Response status:
    - Success: 201
    - Error: 400
Response headers: Location: /users/[id]

### POST /auth/login

Example: http://localhost:8080/auth/login

Request headers:
    - Content-Type: application/json
Request body:

    {
        "username": "foo.bar@example.org",
        "password": "MyPassword12345"
    }


Response body:

    <jwt_token>

Response status:
    - Success: 200
    - Error: 400
