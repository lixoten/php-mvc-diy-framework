# Flexible Gallery Image Architecture

This document outlines a highly flexible and scalable database architecture for managing image galleries. The design allows a single gallery to be associated with multiple, distinct collections of images, each serving a different purpose.

---

## 1. Architecture Diagram

The core of this design involves five tables that work together to create a many-to-many relationship between galleries and images, using "image groups" as the intermediary.

```
+-------------+       +---------------------------------+       +----------------+       +-------------------------+       +----------+
|             |       |                                 |       |                |       |                         |       |          |
|  galleries  |------>|  gallery_image_group_relations  |------>|  image_groups  |------>|  image_group_relations  |------>|  images  |
|             |       |                                 |       |                |       |                         |       |          |
+-------------+       +---------------------------------+       +----------------+       +-------------------------+       +----------+
| id (PK)     |       | gallery_id (FK to galleries.id) |       | id (PK)        |       | image_group_id (FK)     |       | id (PK)  |
| name        |       | image_group_id (FK)             |       | name           |       | image_id (FK to images.id)|       | filename |
| ...         |       | type (e.g. 'main', 'sketches')  |       | ...            |       | sort_order              |       | ...      |
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
  `name` VARCHAR(255) NOT NULL COMMENT 'An internal name for the group, e.g., Summer 2026 Lookbook',
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

### `galleries` Table
This is the main entity table for your galleries.

```sql
CREATE TABLE `galleries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
```

### `gallery_image_group_relations` Table
This is the most critical linking table. It connects a `gallery` to one or more `image_groups` and uses a `type` column to define the *purpose* of that specific connection.

```sql
CREATE TABLE `gallery_image_group_relations` (
  `gallery_id` INT UNSIGNED NOT NULL,
  `image_group_id` INT UNSIGNED NOT NULL,
  `type` VARCHAR(50) NOT NULL COMMENT 'e.g., main_exhibit, behind_the_scenes, sketches',
  PRIMARY KEY (`gallery_id`, `image_group_id`, `type`),
  FOREIGN KEY (`gallery_id`) REFERENCES `galleries`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`image_group_id`) REFERENCES `image_groups`(`id`) ON DELETE CASCADE
);
```

---

## 3. Scenarios for Using Multiple Groups

The power of this design comes from the ability to link a single gallery to multiple image groups, each with a different `type`.

### Scenario 1: Main vs. "Behind the Scenes" Content
A public art gallery might have its main, polished photos in one group and a separate collection of "making of" photos in another.
*   **Group 1 (Type: `main_exhibit`):** The polished, final images that make up the public gallery.
*   **Group 2 (Type: `behind_the_scenes`):** A separate collection showing the setup or the artist at work. This group could be shown only to logged-in members.

### Scenario 2: Collaborations or Guest Artists
A gallery could feature a temporary collaboration with a guest artist without mixing their work into the main collection.
*   **Group 1 (Type: `primary_artist`):** Contains all images created by the main artist for the gallery.
*   **Group 2 (Type: `guest_artist_feature`):** A special group of images contributed by a guest artist.

### Scenario 3: Different Formats or Mediums
A gallery for a single project could separate final photographs from initial concept sketches.
*   **Group 1 (Type: `photographs`):** The main set of photographic prints.
*   **Group 2 (Type: `sketches`):** A supplementary group showing the initial concept sketches or drawings related to the photographs.