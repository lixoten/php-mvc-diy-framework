# Flexible Category Image Architecture

This document outlines a highly flexible and scalable database architecture for managing category images. The design allows a single category to be associated with multiple, distinct collections of images, each serving a different purpose.

---

## 1. Architecture Diagram

The core of this design involves five tables that work together to create a many-to-many relationship between categories and images, using "image groups" as the intermediary.

```
+-------------+       +-----------------------------------+       +----------------+       +-------------------------+       +----------+
|             |       |                                   |       |                |       |                         |       |          |
| categories  |------>|  category_image_group_relations   |------>|  image_groups  |------>|  image_group_relations  |------>|  images  |
|             |       |                                   |       |                |       |                         |       |          |
+-------------+       +-----------------------------------+       +----------------+       +-------------------------+       +----------+
| id (PK)     |       | category_id (FK to categories.id) |       | id (PK)        |       | image_group_id (FK)     |       | id (PK)  |
| name        |       | image_group_id (FK)               |       | name           |       | image_id (FK to images.id)|       | filename |
| ...         |       | type (e.g. 'banner', 'icon')      |       | ...            |       | sort_order              |       | ...      |
+-------------+       +-----------------------------------+       +----------------+       +-------------------------+       +----------+
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
  `name` VARCHAR(255) NOT NULL COMMENT 'An internal name for the group, e.g., Mens Apparel Banner',
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

### `categories` Table
This is the main entity table for your product categories.

```sql
CREATE TABLE `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `parent_id` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
```

### `category_image_group_relations` Table
This is the critical linking table. It connects a `category` to one or more `image_groups` and uses a `type` column to define the *purpose* of that specific connection.

```sql
CREATE TABLE `category_image_group_relations` (
  `category_id` INT UNSIGNED NOT NULL,
  `image_group_id` INT UNSIGNED NOT NULL,
  `type` VARCHAR(50) NOT NULL COMMENT 'e.g., banner, icon, style_guide',
  PRIMARY KEY (`category_id`, `image_group_id`, `type`),
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`image_group_id`) REFERENCES `image_groups`(`id`) ON DELETE CASCADE
);
```

---

## 3. Scenarios for Using Multiple Groups

Here are some practical scenarios where a single category might use multiple, distinct image groups.

### Scenario 1: Banners vs. Icons
A category needs both a large, visually rich banner for its main page and a small, simple icon for menus.
*   **Group 1 (Type: `banner`):** A high-resolution, wide image used at the top of the category page.
*   **Group 2 (Type: `icon`):** A small, square icon used in the main navigation menu or breadcrumbs.

### Scenario 2: Promotional vs. Default Imagery
You can run a holiday promotion without changing the category's main banner.
*   **Group 1 (Type: `default_banner`):** The standard, evergreen banner for the category.
*   **Group 2 (Type: `promo_banner_winter`):** A special banner with a "Winter Sale" theme that you can programmatically display on the category page only during the month of December.

### Scenario 3: Visual Style Guides
A category like "Men's Formal Wear" could have an image group that acts as a style guide.
*   **Group 1 (Type: `banner`):** The main banner image.
*   **Group 2 (Type: `style_guide`):** A curated set of images showing how to pair different items (suits, shirts, ties) within that category, which could be displayed as a separate section on the page.