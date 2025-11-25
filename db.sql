-- 1. Таблица пользователей
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Таблица групп (списков)
CREATE TABLE `groups` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `invite_code` VARCHAR(10) NOT NULL UNIQUE, -- Код для приглашения (должен быть уникальным)
  `owner_id` INT NOT NULL, -- Создатель группы
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Таблица связи "Пользователи <-> Группы" (Кто в какой группе состоит)
CREATE TABLE `group_members` (
  `user_id` INT NOT NULL,
  `group_id` INT NOT NULL,
  `joined_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `group_id`), -- Чтобы нельзя было вступить в группу дважды
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Таблица товаров
CREATE TABLE `items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `group_id` INT NOT NULL, -- К какому списку относится
  `user_id` INT NOT NULL, -- Кто добавил (для истории, можно выводить имя)
  `name` VARCHAR(255) NOT NULL,
  `is_bought` TINYINT(1) DEFAULT 0, -- 0 = нужно купить, 1 = куплено
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Добавляем индексы для улучшения производительности
CREATE INDEX idx_items_group_id ON items(group_id);
CREATE INDEX idx_items_is_bought ON items(is_bought);
CREATE INDEX idx_group_members_user_id ON group_members(user_id);
CREATE INDEX idx_group_members_group_id ON group_members(group_id);