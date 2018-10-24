CREATE TABLE IF NOT EXISTS products (
    id integer PRIMARY KEY,
    name text NOT NULL UNIQUE,
    price integer NOT NULL,
    discount integer NOT NULL,
    discount_type text NOT NULL
);

CREATE TABLE IF NOT EXISTS bundles (
    id integer PRIMARY KEY,
    name text NOT NULL UNIQUE,
    price integer NOT NULL
);

CREATE TABLE IF NOT EXISTS product_bundles (
    product_id integer,
    bundle_id integer,
    PRIMARY KEY (product_id, bundle_id),
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE ON UPDATE NO ACTION,
    FOREIGN KEY (bundle_id) REFERENCES bundles (id) ON DELETE CASCADE ON UPDATE NO ACTION
);

CREATE TABLE IF NOT EXISTS orders (
    id integer PRIMARY KEY,
    total_price integer NOT NULL,
    user_id integer NOT NULL
);

CREATE TABLE IF NOT EXISTS product_orders (
    product_id integer,
    order_id integer,
    PRIMARY KEY (product_id, order_id),
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE ON UPDATE NO ACTION,
    FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE ON UPDATE NO ACTION
);

CREATE TABLE IF NOT EXISTS bundle_orders (
    bundle_id integer,
    order_id integer,
    PRIMARY KEY (bundle_id, order_id),
    FOREIGN KEY (bundle_id) REFERENCES bundles (id) ON DELETE CASCADE ON UPDATE NO ACTION,
    FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE ON UPDATE NO ACTION
);

CREATE TABLE IF NOT EXISTS users (
    id integer PRIMARY KEY,
    username text NOT NULL UNIQUE,
    password text NOT NULL
);

CREATE TABLE IF NOT EXISTS roles (
    id integer PRIMARY KEY,
    role text NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS user_roles (
    user_id integer,
    role_id integer,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE NO ACTION,
    FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE ON UPDATE NO ACTION
);

-- default user, should be removed from db
-- as soon as you create your own admin user

INSERT INTO users (username, password) VALUES (
    'foo.bar@example.org',
    '246172676f6e32696424763d3139246d3d3236323134342c743d332c703d312457516a6870635179354655456b31466c494a686a4a67243347564e4349595a67696872744f437a6a4e416b6c7a6f4265776d4f4d3168435a49467464344647655067'
);
