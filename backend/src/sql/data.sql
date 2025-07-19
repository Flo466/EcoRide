USE ecoride;
/*
Create platform user 
**/
INSERT INTO user (
    email,
    roles,
    password,
    last_name,
    first_name,
    phone,
    address,
    birth_date,
    photo,
    photo_mime_type,
    user_name,
    credits,
    created_at,
    updated_at,
    api_token,
    is_driver
) VALUES (
    'platform@ecoride.com',
    '["ROLE_ADMIN"]',
    '$2y$10$Q6ZWYMrcP3UMQw.j.aF3vOOcj3eqxwD/EOrZo1IxrG2Df9Aj9ktXW',
    'System',
    'Plateforme',
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    'PlatformUser',
    0,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP,
    '112ff156f13c93fb9b9fcd5a50d6fb4608dc5f65843fd15712944d4b437882c3',
    0
);