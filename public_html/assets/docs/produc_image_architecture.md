# Flexible Product Image Architecture

This document outlines a highly flexible and scalable database architecture for managing product images. The design allows a single product to be associated with multiple, distinct collections of images, each serving a different purpose.

---

## 1. Architecture Diagram

The core of this design involves five tables that work together to create a many-to-many relationship between products and images, using "image groups" as the intermediary.

```
+-------------+       +---------------------------------+       +----------------+       +-------------------------+       +----------+
|             |       |                                 |       |                |       |                         |       |          |
|  products   |------>|  product_image_group_relations  |------>|  image_groups  |------>|  image_group_relations  |------>|  images  |
|             |       |                                 |       |                |       |                         |       |          |
+-------------+       +---------------------------------+       +----------------+       +-------------------------+       +----------+
| id (PK)     |       | product_id (FK to products.id)  |       | id (PK)        |       | image_group_id (FK)     |       | id (PK)  |
| name        |       | image_group_id (FK)             |       | name           |       | image_id (FK to images.id)|       | filename |
| ...         |       | type (e.g. 'official', 'customer') |       | ...            |       | sort_order              |       | ...      |
+-------------+       +---------------------------------+       +----------------+       +-------------------------+       +----------+
```

---

## 2. Table Design

Each table has a single, well-defined responsibility, which follows good database normalization principles.

### `images` Table
This is the master table for every individual image file uploaded to the system.

```sql
CREATE TABLE `images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT UNSIGNED NOT NULL,
  `filename` VARCHAR(255) NOT NULL COMMENT 'The secure, unique name on disk',
  `alt_text` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
```

### `image_groups` Table
This table defines a "group" or a "collection." It acts as a named container for images.

```sql
CREATE TABLE `image_groups` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL COMMENT 'An internal name for the group, e.g., Red T-Shirt Gallery',
  `description` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
```

### `image_group_relations` Table
This is a linking table that connects multiple images to a single group, defining the membership and display order of images within that group.

```sql
CREATE TABLE `image_group_relations` (
  `image_group_id` INT UNSIGNED NOT NULL,
  `image_id` INT UNSIGNED NOT NULL,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`image_group_id`, `image_id`),
  FOREIGN KEY (`image_group_id`) REFERENCES `image_groups`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`image_id`) REFERENCES `images`(`id`) ON DELETE CASCADE
);
```

### `products` Table
This is the main entity table for your products.

```sql
CREATE TABLE `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `sku` VARCHAR(100) NULL,
  `price` DECIMAL(10, 2) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
```

### `product_image_group_relations` Table
This is the most critical linking table. It connects a `product` to one or more `image_groups` and uses a `type` column to define the *purpose* of that specific connection.

```sql
CREATE TABLE `product_image_group_relations` (
  `product_id` INT UNSIGNED NOT NULL,
  `image_group_id` INT UNSIGNED NOT NULL,
  `type` VARCHAR(50) NOT NULL COMMENT 'e.g., official_gallery, customer_photos, lookbook',
  PRIMARY KEY (`product_id`, `image_group_id`, `type`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`image_group_id`) REFERENCES `image_groups`(`id`) ON DELETE CASCADE
);
```

---

## 3. Scenarios for Using Multiple Groups

The power of this design comes from the ability to link a single product to multiple image groups, each with a different `type`.

### Scenario 1: Official vs. User-Generated Content
A product page can show professional studio shots in the main gallery and have a separate, distinct section for photos submitted by customers.
*   **Group 1 (Type: `official_gallery`):** High-quality studio and lifestyle shots that you control.
*   **Group 2 (Type: `customer_photos`):** Photos submitted by actual customers, which builds trust and social proof.

### Scenario 2: Marketing Campaigns
You can create special image groups for seasonal campaigns without altering the main product gallery.
*   **Group 1 (Type: `main`):** The standard, evergreen product photos.
*   **Group 2 (Type: `lookbook_summer_2026`):** A special, curated set of images showing the product as part of a seasonal "lookbook" or style guide.

### Scenario 3: Different Product Aspects
For complex items, you can separate the main product shots from instructional or detailed images.
*   **Group 1 (Type: `product_shots`):** The primary images showing the product itself.
*   **Group 2 (Type: `how_to_use`):** A step-by-step visual guide on how to assemble or use the product.
*   **Group 3 (Type: `packaging`):** Photos specifically showing the product's packaging, which is important for gifts or high-end items.