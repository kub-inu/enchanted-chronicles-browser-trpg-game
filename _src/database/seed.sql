/* Add all roles */
INSERT INTO roles (id, name) VALUES
(1, 'superadmin'), (2, 'admin'), (3, 'moderator'), (4, 'player');

/* Insert all App permissions flags */
INSERT INTO permissions (name) VALUES
('admin.access'),
('user.read'), ('user.update'), ('user.delete'),
('character.create'), ('character.read'), ('character.update'), ('character.delete'), ('character.list');

-- superadmin
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, p.id
FROM permissions p;

-- admin
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, p.id
FROM permissions p
WHERE p.name IN (
    'admin.access',
    'user.read',
    'user.update',
    'user.delete',
    'character.create',
    'character.read',
    'character.update',
    'character.delete',
    'character.list'
);

-- moderator
INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, p.id
FROM permissions p
WHERE p.name IN (
    'user.read',
    'character.read',
    'character.update',
    'character.list'
);

-- player
INSERT INTO role_permissions (role_id, permission_id)
SELECT 4, p.id
FROM permissions p
WHERE p.name IN (
    'character.create',
    'character.read',
    'character.update',
    'character.delete',
    'character.list'
);
