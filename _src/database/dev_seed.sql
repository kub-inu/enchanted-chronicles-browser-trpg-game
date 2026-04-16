/* Insert demo admin account */
INSERT INTO users ( role_id, username, email, password, legal ) VALUES
(1, 'superadmin', 'superadmin@example.com', '$2y$10$T1Ky6F56ls2gEzj3RX6za.U6hWO3EhKVJtJtug4Y2QQHTl.cdSuTq', 1),
(2, 'admin', 'admin@example.com', '$2y$10$T1Ky6F56ls2gEzj3RX6za.U6hWO3EhKVJtJtug4Y2QQHTl.cdSuTq', 1),
(3, 'moderator', 'moderator@example.com', '$2y$10$T1Ky6F56ls2gEzj3RX6za.U6hWO3EhKVJtJtug4Y2QQHTl.cdSuTq', 1),
(4, 'player', 'player@example.com', '$2y$10$T1Ky6F56ls2gEzj3RX6za.U6hWO3EhKVJtJtug4Y2QQHTl.cdSuTq', 1);


INSERT INTO characters ( user_id, first_name, middle_name, last_name, gender, birthday ) VALUES
-- user 1
(1, 'Arthas', 'Goel', 'Menethil', 0, '1995-05-12'),
(1, 'Jaina', NULL, 'Proudmoore', 1, '1996-08-23'),
(1, 'Uther', NULL, 'Lightbringer', 0, '1975-11-03'),

-- user 2
(2, 'Sylvanas', NULL, 'Windrunner', 1, '1988-02-14');