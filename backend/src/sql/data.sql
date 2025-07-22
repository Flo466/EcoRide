-- Use the ecoride database
USE `ecoride`;

-- Temporarily disable foreign key checks for data insertion
SET FOREIGN_KEY_CHECKS = 0;

--
-- Insert brands
--
INSERT IGNORE INTO brand (label) VALUES
('Abarth'), ('Aiways'), ('Alfa Romeo'), ('Alpine'), ('Aston Martin'), ('Audi'), ('Bentley'), ('BMW'), ('BYD'),
('Cadillac'), ('Chevrolet'), ('Citroën'), ('Dacia'), ('DS Automobiles'), ('Ferrari'), ('Fiat'), ('Ford'), ('Honda'),
('Hyundai'), ('Infiniti'), ('Isuzu'), ('Jaguar'), ('Jeep'), ('Kia'), ('Lamborghini'), ('Lancia'), ('Land Rover'),
('Lexus'), ('Lotus'), ('Maserati'), ('Mazda'), ('McLaren'), ('Mercedes-Benz'), ('MG Motor'), ('Mini'),
('Mitsubishi'), ('Nissan'), ('Nio'), ('Opel'), ('Ora'), ('Peugeot'), ('Polestar'), ('Porsche'), ('Renault'),
('Rolls-Royce'), ('Seat'), ('Seres'), ('Skoda'), ('Smart'), ('SsangYong'), ('Subaru'), ('Suzuki'), ('Tesla'),
('Toyota'), ('Volkswagen'), ('Volvo'), ('Xpeng'), ('Zeekr');

--
-- Insert the "Platform" user with forced ID 1
--
INSERT IGNORE INTO `user` (
    `id`, `email`, `roles`, `password`, `user_name`, `credits`, `created_at`, `api_token`, `is_driver`,
    `last_name`, `first_name`, `phone`, `address`, `birth_date`, `photo`, `photo_mime_type`, `updated_at`, `used_car_id`
) VALUES (
    1,
    'platform@ecoride.com',
    '["ROLE_ADMIN"]',
    '$2y$10$Q6ZWYMrcP3UMQw.j.aF3vOOcj3eqxwD/EOrZo1IxrG2Df9Aj9ktXW',
    'PlatformUser',
    0,
    NOW(),
    'e0f9c8d7a6b5c4d3e2f1a0b9c8d7e6f5a4b3c2d1e0f9c8d7a6b5c4d3e2f1a0b9',
    FALSE,
    'System', 'Platform', NULL, NULL, NULL, NULL, NULL, NULL, NULL
);

--
-- Insert 20 regular users
--
INSERT INTO `user` (
    `email`, `roles`, `password`, `last_name`, `first_name`, `phone`, `address`, `birth_date`,
    `photo`, `photo_mime_type`, `user_name`, `credits`, `created_at`, `updated_at`, `api_token`, `is_driver`, `used_car_id`
) VALUES
-- 10 Drivers / Passengers
('john.doe@example.com', '["ROLE_USER"]', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.h7x/h1C/.', 'Doe', 'John', '0700000001', '10 Elm Street, London', '1985-01-15 00:00:00', NULL, NULL, 'JohnD', 50, NOW(), NULL, 'Tkn_johndoe_123abc456def7890', TRUE, NULL),
('jane.smith@example.com', '["ROLE_USER"]', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.h7x/h1C/.', 'Smith', 'Jane', '0700000002', '22 Oak Avenue, Manchester', '1990-03-20 00:00:00', NULL, NULL, 'JaneS', 50, NOW(), NULL, 'Tkn_janesmith_abc123def456789', TRUE, NULL),
('mike.brown@example.com', '["ROLE_USER"]', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.h7x/h1C/.', 'Brown', 'Mike', '0700000003', '33 Pine Lane, Birmingham', '1988-07-01 00:00:00', NULL, NULL, 'MikeB', 50, NOW(), NULL, 'Tkn_mikebrown_456ghi789jkl0123', TRUE, NULL),
('sarah.white@example.com', '["ROLE_USER"]', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.h7x/h1C/.', 'White', 'Sarah', '0700000004', '44 Cedar Road, Leeds', '1992-11-10 00:00:00', NULL, NULL, 'SarahW', 50, NOW(), NULL, 'Tkn_sarahwhite_mno789pqr012345', TRUE, NULL),
('chris.green@example.com', '["ROLE_USER"]', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.h7x/h1C/.', 'Green', 'Chris', '0700000005', '55 Birch Street, Glasgow', '1983-04-25 00:00:00', NULL, NULL, 'ChrisG', 50, NOW(), NULL, 'Tkn_chrisgreen_stu456vwx789012', TRUE, NULL),
('emily.black@example.com', '["ROLE_USER"]', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.h7x/h1C/.', 'Black', 'Emily', '0700000006', '66 Willow Drive, Edinburgh', '1995-09-05 00:00:00', NULL, NULL, 'EmilyB', 50, NOW(), NULL, 'Tkn_emilyblack_yza012bcd345678', TRUE, NULL),
('daniel.grey@example.com', '["ROLE_USER"]', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.h7x/h1C/.', 'Grey', 'Daniel', '0700000007', '77 Spruce Court, Bristol', '1987-02-28 00:00:00', NULL, NULL, 'DanielG', 50, NOW(), NULL, 'Tkn_danielgrey_efg901hij234567', TRUE, NULL),
('olivia.blue@example.com', '["ROLE_USER"]', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.h7x/h1C/.', 'Blue', 'Olivia', '0700000008', '88 Poplar Place, Cardiff', '1991-06-12 00:00:00', NULL, NULL, 'OliviaB', 50, NOW(), NULL, 'Tkn_oliviablu_klm890nop123456', TRUE, NULL),
('william.red@example.com', '["ROLE_USER"]', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.h7x/h1C/.', 'Red', 'William', '0700000009', '99 Ash Street, Belfast', '1984-10-03 00:00:00', NULL, NULL, 'WilliamR', 50, NOW(), NULL, 'Tkn_williamred_qrs789tuv012345', TRUE, NULL),
('sophia.yellow@example.com', '["ROLE_USER"]', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.h7x/h1C/.', 'Yellow', 'Sophia', '0700000010', '100 Beach Road, Brighton', '1993-08-18 00:00:00', NULL, NULL, 'SophiaY', 50, NOW(), NULL, 'Tkn_sophiayellow_wxy678zab90123', TRUE, NULL),
-- 10 Passengers
('liam.jones@example.com', '["ROLE_USER"]', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.h7x/h1C/.', 'Jones', 'Liam', '0700000011', '111 River Street, Dublin', '1990-02-01 00:00:00', NULL, NULL, 'LiamJ', 20, NOW(), NULL, 'Tkn_liamjones_abc456def7890123', FALSE, NULL),
('chloe.davis@example.com', '["ROLE_USER"]', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.h7x/h1C/.', 'Davis', 'Chloe', '0700000012', '122 Hillside Road, Cork', '1994-05-10 00:00:00', NULL, NULL, 'ChloeD', 20, NOW(), NULL, 'Tkn_chloedavis_ghi123jkl4567890', FALSE, NULL),
('ben.miller@example.com', '["ROLE_USER"]', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.h7x/h1C/.', 'Miller', 'Ben', '0700000013', '133 Valley View, Galway', '1986-09-22 00:00:00', NULL, NULL, 'BenM', 20, NOW(), NULL, 'Tkn_benmiller_mno789pqr0123456', FALSE, NULL),
('mia.wilson@example.com', '["ROLE_USER"]', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.h7x/h1C/.', 'Wilson', 'Mia', '0700000014', '144 Lake Drive, Limerick', '1991-11-03 00:00:00', NULL, NULL, 'MiaW', 20, NOW(), NULL, 'Tkn_miawilson_stu456vwx7890123', FALSE, NULL),
('noah.moore@example.com', '["ROLE_USER"]', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.h7x/h1C/.', 'Moore', 'Noah', '0700000015', '155 Forest Lane, Waterford', '1989-01-08 00:00:00', NULL, NULL, 'NoahM', 20, NOW(), NULL, 'Tkn_noahmoore_yza012bcd3456789', FALSE, NULL),
('isabella.taylor@example.com', '["ROLE_USER"]', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.h7x/h1C/.', 'Taylor', 'Isabella', '0700000016', '166 Park Road, Kilkenny', '1993-04-17 00:00:00', NULL, NULL, 'IsabellaT', 20, NOW(), NULL, 'Tkn_isabellataylor_efg901hij234567', FALSE, NULL),
('james.anderson@example.com', '["ROLE_USER"]', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.h7x/h1C/.', 'Anderson', 'James', '0700000017', '177 Garden Path, Sligo', '1982-07-29 00:00:00', NULL, NULL, 'JamesA', 20, NOW(), NULL, 'Tkn_jamesanderson_klm890nop1234567', FALSE, NULL),
('ava.thomas@example.com', '["ROLE_USER"]', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.h7x/h1C/.', 'Thomas', 'Ava', '0700000018', '188 Bridge Street, Ennis', '1996-10-05 00:00:00', NULL, NULL, 'AvaT', 20, NOW(), NULL, 'Tkn_avathomas_qrs789tuv01234567', FALSE, NULL),
('logan.jackson@example.com', '["ROLE_USER"]', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.h7x/h1C/.', 'Jackson', 'Logan', '0700000019', '199 Old Road, Tralee', '1987-12-14 00:00:00', NULL, NULL, 'LoganJ', 20, NOW(), NULL, 'Tkn_loganjackson_wxy678zab9012345', FALSE, NULL),
('sophie.harris@example.com', '["ROLE_USER"]', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.h7x/h1C/.', 'Harris', 'Sophie', '0700000020', '200 New Street, Athlone', '1995-03-28 00:00:00', NULL, NULL, 'SophieH', 20, NOW(), NULL, 'Tkn_sophieharris_cde345fgh6789012', FALSE, NULL),
-- test user
('test@test.com', '["ROLE_USER"]', '$2y$13$/ewjqhF8tGwz7sByjOwqoeBAzc5mIMPpzO8qeBr0IyBowd5OWEFNq', 'Testeur', 'Compte', '0700000020', '200 test Street, Test City', '1995-03-28 00:00:00', NULL, NULL, 'Test', 20, NOW(), NULL, 'Tkn_testeur_cde345fgh6789012', FALSE, NULL);

--
-- Insert 10 vehicles
--
INSERT INTO `car` (
    `user_id`, `brand_id`, `model`, `color`, `licence_plate`, `energy`, `first_registration_date`,
    `created_at`, `updated_at`, `pets_allowed`, `seats`
) VALUES
(2, (SELECT id FROM `brand` WHERE label = 'Tesla'), 'Model 3', 'Noir', 'AZ123BC', 'Électrique', '2022-01-15', NOW(), NULL, FALSE, 3),
(3, (SELECT id FROM `brand` WHERE label = 'Toyota'), 'Corolla', 'Blanc', 'BY456DE', 'Hybride', '2021-03-20', NOW(), NULL, FALSE, 3),
(4, (SELECT id FROM `brand` WHERE label = 'Volkswagen'), 'Golf', 'Bleu', 'CX789FG', 'Essence', '2020-07-01', NOW(), NULL, FALSE, 3),
(5, (SELECT id FROM `brand` WHERE label = 'Renault'), 'Zoe', 'Rouge', 'DW012HI', 'Électrique', '2023-02-01', NOW(), NULL, FALSE, 3),
(6, (SELECT id FROM `brand` WHERE label = 'Peugeot'), '308', 'Gris', 'EV345JK', 'Diesel', '2019-09-10', NOW(), NULL, FALSE, 2),
(7, (SELECT id FROM `brand` WHERE label = 'Nissan'), 'Leaf', 'Argent', 'FZ678LM', 'Électrique', '2022-05-25', NOW(), NULL, FALSE, 3),
(8, (SELECT id FROM `brand` WHERE label = 'Dacia'), 'Sandero Stepway', 'Orange', 'GA901NO', 'GPL', '2021-11-12', NOW(), NULL, FALSE, 3),
(9, (SELECT id FROM `brand` WHERE label = 'BMW'), 'i4', 'Vert', 'HB234PQ', 'Électrique', '2024-01-05', NOW(), NULL, FALSE, 2),
(10, (SELECT id FROM `brand` WHERE label = 'Ford'), 'Fiesta', 'Jaune', 'IC567RS', 'Essence', '2018-06-30', NOW(), NULL, FALSE, 3),
(11, (SELECT id FROM `brand` WHERE label = 'Hyundai'), 'Kona', 'Citron', 'JD890TU', 'Électrique', '2023-09-01', NOW(), NULL, FALSE, 2);

--
-- Insert 3 carpooligs per driver
--
INSERT INTO `carpooling` (
    `car_id`, `departure_date`, `departure_time`, `departure_place`,
    `arrival_date`, `arrival_time`, `arrival_place`,
    `seat_count`, `price_per_person`, `is_eco`, `created_at`, `updated_at`, `status`
) VALUES
-- Carpoolings for John Doe (User ID: 2, Car: Tesla Model 3) - Departing from Paris
((SELECT id FROM `car` WHERE user_id = 2 AND model = 'Model 3'), '2025-09-01', '07:30:00', 'Paris', '2025-09-01', '08:15:00', 'Boulogne-Billancourt', 3, 5.00, TRUE, NOW(), NULL, 'open'),
((SELECT id FROM `car` WHERE user_id = 2 AND model = 'Model 3'), '2025-09-01', '09:00:00', 'Paris', '2025-09-01', '10:00:00', 'Saint-Denis', 3, 7.00, TRUE, NOW(), NULL, 'open'),
((SELECT id FROM `car` WHERE user_id = 2 AND model = 'Model 3'), '2025-09-01', '14:00:00', 'Paris', '2025-09-01', '15:30:00', 'Versailles', 3, 10.00, TRUE, NOW(), NULL, 'open'),

-- Carpoolings for Jane Smith (User ID: 3, Car: Toyota Corolla) - Departing from Lyon
((SELECT id FROM `car` WHERE user_id = 3 AND model = 'Corolla'), '2025-09-02', '08:00:00', 'Lyon', '2025-09-02', '09:00:00', 'Villeurbanne', 4, 6.00, FALSE, NOW(), NULL, 'open'),
((SELECT id FROM `car` WHERE user_id = 3 AND model = 'Corolla'), '2025-09-02', '10:00:00', 'Lyon', '2025-09-02', '11:00:00', 'Vénissieux', 4, 6.00, FALSE, NOW(), NULL, 'open'),
((SELECT id FROM `car` WHERE user_id = 3 AND model = 'Corolla'), '2025-09-02', '15:00:00', 'Lyon', '2025-09-02', '16:30:00', 'Saint-Étienne', 4, 15.00, FALSE, NOW(), NULL, 'open'),

-- Carpoolings for Mike Brown (User ID: 4, Car: Volkswagen Golf) - Departing from Marseille
((SELECT id FROM `car` WHERE user_id = 4 AND model = 'Golf'), '2025-09-03', '07:00:00', 'Marseille', '2025-09-03', '08:30:00', 'Aix-en-Provence', 3, 8.00, FALSE, NOW(), NULL, 'open'),
((SELECT id FROM `car` WHERE user_id = 4 AND model = 'Golf'), '2025-09-03', '09:00:00', 'Marseille', '2025-09-03', '10:30:00', 'Toulon', 3, 12.00, FALSE, NOW(), NULL, 'open'),
((SELECT id FROM `car` WHERE user_id = 4 AND model = 'Golf'), '2025-09-03', '14:00:00', 'Marseille', '2025-09-03', '17:00:00', 'Nice', 3, 25.00, FALSE, NOW(), NULL, 'open'),

-- Carpoolings for Sarah White (User ID: 5, Car: Renault Zoe) - Departing from Toulouse
((SELECT id FROM `car` WHERE user_id = 5 AND model = 'Zoe'), '2025-09-04', '08:00:00', 'Toulouse', '2025-09-04', '09:30:00', 'Albi', 2, 10.00, TRUE, NOW(), NULL, 'open'),
((SELECT id FROM `car` WHERE user_id = 5 AND model = 'Zoe'), '2025-09-04', '10:00:00', 'Toulouse', '2025-09-04', '12:00:00', 'Carcassonne', 2, 15.00, TRUE, NOW(), NULL, 'open'),
((SELECT id FROM `car` WHERE user_id = 5 AND model = 'Zoe'), '2025-09-04', '15:00:00', 'Toulouse', '2025-09-04', '17:30:00', 'Montpellier', 2, 20.00, TRUE, NOW(), NULL, 'open'),

-- Carpoolings for Chris Green (User ID: 6, Car: Peugeot 308) - Departing from Rouen
((SELECT id FROM `car` WHERE user_id = 6 AND model = '308'), '2025-09-05', '07:15:00', 'Rouen', '2025-09-05', '08:30:00', 'Le Havre', 4, 10.00, FALSE, NOW(), NULL, 'open'),
((SELECT id FROM `car` WHERE user_id = 6 AND model = '308'), '2025-09-05', '09:00:00', 'Rouen', '2025-09-05', '10:30:00', 'Caen', 4, 12.00, FALSE, NOW(), NULL, 'open'),
((SELECT id FROM `car` WHERE user_id = 6 AND model = '308'), '2025-09-05', '14:00:00', 'Rouen', '2025-09-05', '16:00:00', 'Orléans', 4, 20.00, FALSE, NOW(), NULL, 'open'),

-- Carpoolings for Emily Black (User ID: 7, Car: Nissan Leaf) - Departing from Lille
((SELECT id FROM `car` WHERE user_id = 7 AND model = 'Leaf'), '2025-09-06', '08:30:00', 'Lille', '2025-09-06', '09:30:00', 'Amiens', 3, 8.00, TRUE, NOW(), NULL, 'open'),
((SELECT id FROM `car` WHERE user_id = 7 AND model = 'Leaf'), '2025-09-06', '10:00:00', 'Lille', '2025-09-06', '11:00:00', 'Arras', 3, 7.00, TRUE, NOW(), NULL, 'open'),
((SELECT id FROM `car` WHERE user_id = 7 AND model = 'Leaf'), '2025-09-06', '15:00:00', 'Lille', '2025-09-06', '16:30:00', 'Bruxelles', 3, 15.00, TRUE, NOW(), NULL, 'open'),

-- Carpoolings for Daniel Grey (User ID: 8, Car: Dacia Sandero Stepway) - Departing from Dijon
((SELECT id FROM `car` WHERE user_id = 8 AND model = 'Sandero Stepway'), '2025-09-07', '07:00:00', 'Dijon', '2025-09-07', '08:30:00', 'Besançon', 4, 10.00, FALSE, NOW(), NULL, 'open'),
((SELECT id FROM `car` WHERE user_id = 8 AND model = 'Sandero Stepway'), '2025-09-07', '09:00:00', 'Dijon', '2025-09-07', '10:30:00', 'Chalon-sur-Saône', 4, 8.00, FALSE, NOW(), NULL, 'open'),
((SELECT id FROM `car` WHERE user_id = 8 AND model = 'Sandero Stepway'), '2025-09-07', '14:00:00', 'Dijon', '2025-09-07', '16:00:00', 'Lyon', 4, 20.00, FALSE, NOW(), NULL, 'open'),

-- Carpoolings for Olivia Blue (User ID: 9, Car: BMW i4) - Departing from Brest
((SELECT id FROM `car` WHERE user_id = 9 AND model = 'i4'), '2025-09-08', '08:00:00', 'Brest', '2025-09-08', '09:00:00', 'Plouzané', 3, 5.00, TRUE, NOW(), NULL, 'open'),
((SELECT id FROM `car` WHERE user_id = 9 AND model = 'i4'), '2025-09-08', '09:30:00', 'Brest', '2025-09-08', '10:30:00', 'Le Conquet', 3, 7.00, TRUE, NOW(), NULL, 'open'),
((SELECT id FROM `car` WHERE user_id = 9 AND model = 'i4'), '2025-09-08', '14:00:00', 'Brest', '2025-09-08', '16:00:00', 'Douarnenez', 3, 15.00, TRUE, NOW(), NULL, 'open'),

-- Carpoolings for William Red (User ID: 10, Car: Ford Fiesta) - Departing from Nantes
((SELECT id FROM `car` WHERE user_id = 10 AND model = 'Fiesta'), '2025-09-09', '07:30:00', 'Nantes', '2025-09-09', '09:00:00', 'Angers', 4, 10.00, FALSE, NOW(), NULL, 'open'),
((SELECT id FROM `car` WHERE user_id = 10 AND model = 'Fiesta'), '2025-09-09', '09:30:00', 'Nantes', '2025-09-09', '11:00:00', 'Saint-Nazaire', 4, 8.00, FALSE, NOW(), NULL, 'open'),
((SELECT id FROM `car` WHERE user_id = 10 AND model = 'Fiesta'), '2025-09-09', '14:00:00', 'Nantes', '2025-09-09', '16:30:00', 'Rennes', 4, 20.00, FALSE, NOW(), NULL, 'open'),

-- Carpoolings for Sophia Yellow (User ID: 11, Car: Hyundai Kona Electric) - Departing from Strasbourg
((SELECT id FROM `car` WHERE user_id = 11 AND model = 'Kona'), '2025-09-10', '08:00:00', 'Strasbourg', '2025-09-10', '09:00:00', 'Schiltigheim', 4, 5.00, TRUE, NOW(), NULL, 'open'),
((SELECT id FROM `car` WHERE user_id = 11 AND model = 'Kona'), '2025-09-10', '09:30:00', 'Strasbourg', '2025-09-10', '10:30:00', 'Illkirch-Graffenstaden', 4, 6.00, TRUE, NOW(), NULL, 'open'),
((SELECT id FROM `car` WHERE user_id = 11 AND model = 'Kona'), '2025-09-10', '14:00:00', 'Strasbourg', '2025-09-10', '16:00:00', 'Colmar', 4, 15.00, TRUE, NOW(), NULL, 'open');


INSERT INTO `carpooling_user` (
    `user_id`, `carpooling_id`, `is_driver`, `is_cancelled`
) VALUES
-- For each carpooling, the driver is associated
-- John Doe (ID 2)
(2, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 2 AND model = 'Model 3') AND departure_place = 'Paris' AND arrival_place = 'Boulogne-Billancourt'), TRUE, FALSE),
(2, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 2 AND model = 'Model 3') AND departure_place = 'Paris' AND arrival_place = 'Saint-Denis'), TRUE, FALSE),
(2, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 2 AND model = 'Model 3') AND departure_place = 'Paris' AND arrival_place = 'Versailles'), TRUE, FALSE),
-- Jane Smith (ID 3)
(3, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 3 AND model = 'Corolla') AND departure_place = 'Lyon' AND arrival_place = 'Villeurbanne'), TRUE, FALSE),
(3, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 3 AND model = 'Corolla') AND departure_place = 'Lyon' AND arrival_place = 'Vénissieux'), TRUE, FALSE),
(3, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 3 AND model = 'Corolla') AND departure_place = 'Lyon' AND arrival_place = 'Saint-Étienne'), TRUE, FALSE),
-- Mike Brown (ID 4)
(4, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 4 AND model = 'Golf') AND departure_place = 'Marseille' AND arrival_place = 'Aix-en-Provence'), TRUE, FALSE),
(4, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 4 AND model = 'Golf') AND departure_place = 'Marseille' AND arrival_place = 'Toulon'), TRUE, FALSE),
(4, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 4 AND model = 'Golf') AND departure_place = 'Marseille' AND arrival_place = 'Nice'), TRUE, FALSE),
-- Sarah White (ID 5)
(5, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 5 AND model = 'Zoe') AND departure_place = 'Toulouse' AND arrival_place = 'Albi'), TRUE, FALSE),
(5, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 5 AND model = 'Zoe') AND departure_place = 'Toulouse' AND arrival_place = 'Carcassonne'), TRUE, FALSE),
(5, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 5 AND model = 'Zoe') AND departure_place = 'Toulouse' AND arrival_place = 'Montpellier'), TRUE, FALSE),
-- Chris Green (ID 6)
(6, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 6 AND model = '308') AND departure_place = 'Rouen' AND arrival_place = 'Le Havre'), TRUE, FALSE),
(6, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 6 AND model = '308') AND departure_place = 'Rouen' AND arrival_place = 'Caen'), TRUE, FALSE),
(6, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 6 AND model = '308') AND departure_place = 'Rouen' AND arrival_place = 'Orléans'), TRUE, FALSE),
-- Emily Black (ID 7)
(7, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 7 AND model = 'Leaf') AND departure_place = 'Lille' AND arrival_place = 'Amiens'), TRUE, FALSE),
(7, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 7 AND model = 'Leaf') AND departure_place = 'Lille' AND arrival_place = 'Arras'), TRUE, FALSE),
(7, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 7 AND model = 'Leaf') AND departure_place = 'Lille' AND arrival_place = 'Bruxelles'), TRUE, FALSE),
-- Daniel Grey (ID 8)
(8, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 8 AND model = 'Sandero Stepway') AND departure_place = 'Dijon' AND arrival_place = 'Besançon'), TRUE, FALSE),
(8, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 8 AND model = 'Sandero Stepway') AND departure_place = 'Dijon' AND arrival_place = 'Chalon-sur-Saône'), TRUE, FALSE),
(8, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 8 AND model = 'Sandero Stepway') AND departure_place = 'Dijon' AND arrival_place = 'Lyon'), TRUE, FALSE),
-- Olivia Blue (ID 9)
(9, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 9 AND model = 'i4') AND departure_place = 'Brest' AND arrival_place = 'Plouzané'), TRUE, FALSE),
(9, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 9 AND model = 'i4') AND departure_place = 'Brest' AND arrival_place = 'Le Conquet'), TRUE, FALSE),
(9, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 9 AND model = 'i4') AND departure_place = 'Brest' AND arrival_place = 'Douarnenez'), TRUE, FALSE),
-- William Red (ID 10)
(10, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 10 AND model = 'Fiesta') AND departure_place = 'Nantes' AND arrival_place = 'Angers'), TRUE, FALSE),
(10, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 10 AND model = 'Fiesta') AND departure_place = 'Nantes' AND arrival_place = 'Saint-Nazaire'), TRUE, FALSE),
(10, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 10 AND model = 'Fiesta') AND departure_place = 'Nantes' AND arrival_place = 'Rennes'), TRUE, FALSE),
-- Sophia Yellow (ID 11)
(11, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 11 AND model = 'Kona') AND departure_place = 'Strasbourg' AND arrival_place = 'Schiltigheim'), TRUE, FALSE),
(11, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 11 AND model = 'Kona') AND departure_place = 'Strasbourg' AND arrival_place = 'Illkirch-Graffenstaden'), TRUE, FALSE),
(11, (SELECT id FROM `carpooling` WHERE car_id = (SELECT id FROM `car` WHERE user_id = 11 AND model = 'Kona') AND departure_place = 'Strasbourg' AND arrival_place = 'Colmar'), TRUE, FALSE);

INSERT INTO `review` (
    `comment`, `ratting`, `status`, `created_at`, `updated_at`, `user_id`, `reviewed_user_id`
) VALUES
-- Reviews passenger 12 (Bob Johnson)
("À l'heure, excellent !", 5, 'approved', '2025-01-01 09:00:00', NULL, 12, 2),
("Voyage super agréable, confortable et sûr. Je recommande vivement.", 5, 'approved', '2025-01-01 09:05:00', NULL, 12, 2),
("Très ponctuel, c'est agréable !", 5, 'approved', '2025-01-02 10:00:00', NULL, 12, 3),
("Expérience plaisante, arrivé à l'heure et la conversation était intéressante.", 5, 'approved', '2025-01-02 10:05:00', NULL, 12, 3),
("Conducteur fantastique, a rendu le voyage agréable.", 5, 'approved', '2025-01-03 11:00:00', NULL, 12, 4),
("Toujours fiable et professionnel.", 5, 'approved', '2025-01-03 11:05:00', NULL, 12, 4),
("Service de première classe, rien à redire !", 5, 'approved', '2025-01-04 12:00:00', NULL, 12, 5),
("Le conducteur était en retard et la voiture n'était pas très propre.", 2, 'approved', '2025-01-04 12:05:00', NULL, 12, 5),
("Super sympa et serviable.", 5, 'approved', '2025-01-05 13:00:00', NULL, 12, 6),
("Voyage confortable et efficace.", 5, 'approved', '2025-01-05 13:05:00', NULL, 12, 6),
("Trajet parfait, très doux.", 5, 'approved', '2025-01-06 14:00:00', NULL, 12, 7),
("Très bonne ambiance à bord.", 5, 'approved', '2025-01-06 14:05:00', NULL, 12, 7),
("Une conduite souple et sécurisante.", 5, 'approved', '2025-01-07 15:00:00', NULL, 12, 8),
("Arrivé à destination plus tôt que prévu.", 5, 'approved', '2025-01-07 15:05:00', NULL, 12, 8),
("Tout simplement parfait.", 5, 'approved', '2025-01-08 16:00:00', NULL, 12, 9),
("Un grand merci au conducteur !", 5, 'approved', '2025-01-08 16:05:00', NULL, 12, 9),
("Conduite exemplaire, je me suis senti en sécurité tout le long.", 5, 'approved', '2025-01-09 17:00:00', NULL, 12, 10),
("Personne très agréable et attentive aux passagers.", 5, 'approved', '2025-01-09 17:05:00', NULL, 12, 10),
("La voiture était confortable et l'itinéraire bien choisi.", 5, 'approved', '2025-01-10 18:00:00', NULL, 12, 11),
("Ponctualité irréprochable et excellent sens du service.", 5, 'approved', '2025-01-10 18:05:00', NULL, 12, 11),

-- Reviews passenger 13 (Alice Williams)
("À l'heure, excellent !", 5, 'approved', '2025-01-11 09:00:00', NULL, 13, 2),
("Voyage super agréable, confortable et sûr. Je recommande vivement.", 5, 'approved', '2025-01-11 09:05:00', NULL, 13, 2),
("Trajet fluide, le conducteur était très poli et la voiture propre.", 5, 'approved', '2025-01-12 10:00:00', NULL, 13, 3),
("Expérience plaisante, arrivé à l'heure et la conversation était intéressante.", 5, 'approved', '2025-01-12 10:05:00', NULL, 13, 3),
("Conduite brusque, le conducteur semblait distrait.", 2, 'approved', '2025-01-13 11:00:00', NULL, 13, 4),
("Toujours fiable et professionnel.", 5, 'approved', '2025-01-13 11:05:00', NULL, 13, 4),
("Service de première classe, rien à redire !", 5, 'approved', '2025-01-14 12:00:00', NULL, 13, 5),
("Super sympa et serviable.", 5, 'approved', '2025-01-14 12:05:00', NULL, 13, 5),
("Voyage confortable et efficace.", 5, 'approved', '2025-01-15 13:00:00', NULL, 13, 6),
("Trajet parfait, très doux.", 5, 'approved', '2025-01-15 13:05:00', NULL, 13, 6),
("Très bonne ambiance à bord.", 5, 'approved', '2025-01-16 14:00:00', NULL, 13, 7),
("Une conduite souple et sécurisante.", 5, 'approved', '2025-01-16 14:05:00', NULL, 13, 7),
("Arrivé à destination plus tôt que prévu.", 5, 'approved', '2025-01-17 15:00:00', NULL, 13, 8),
("Tout simplement parfait.", 5, 'approved', '2025-01-17 15:05:00', NULL, 13, 8),
("Un grand merci au conducteur !", 5, 'approved', '2025-01-18 16:00:00', NULL, 13, 9),
("La musique était trop forte et le trajet n'était pas confortable.", 2, 'approved', '2025-01-18 16:05:00', NULL, 13, 9),
("Conduite exemplaire, je me suis senti en sécurité tout le long.", 5, 'approved', '2025-01-19 17:00:00', NULL, 13, 10),
("Personne très agréable et attentive aux passagers.", 5, 'approved', '2025-01-19 17:05:00', NULL, 13, 10),
("La voiture était confortable et l'itinéraire bien choisi.", 5, 'approved', '2025-01-20 18:00:00', NULL, 13, 11),
("Ponctualité irréprochable et excellent sens du service.", 5, 'approved', '2025-01-20 18:05:00', NULL, 13, 11),

-- Reviews passenger 14 (Charlie Davis)
("À l'heure, excellent !", 5, 'approved', '2025-01-21 09:00:00', NULL, 14, 2),
("Voyage super agréable, confortable et sûr. Je recommande vivement.", 5, 'approved', '2025-01-21 09:05:00', NULL, 14, 2),
("Trajet fluide, le conducteur était très poli et la voiture propre.", 5, 'approved', '2025-01-22 10:00:00', NULL, 14, 3),
("Pas une super expérience, le conducteur était impoli.", 2, 'approved', '2025-01-22 10:05:00', NULL, 14, 3),
("Conducteur fantastique, a rendu le voyage agréable.", 5, 'approved', '2025-01-23 11:00:00', NULL, 14, 4),
("Toujours fiable et professionnel.", 5, 'approved', '2025-01-23 11:05:00', NULL, 14, 4),
("Service de première classe, rien à redire !", 5, 'approved', '2025-01-24 12:00:00', NULL, 14, 5),
("Super sympa et serviable.", 5, 'approved', '2025-01-24 12:05:00', NULL, 14, 5),
("La voiture sentait le tabac, ce n'était pas agréable.", 2, 'approved', '2025-01-25 13:00:00', NULL, 14, 6),
("Voyage confortable et efficace.", 5, 'approved', '2025-01-25 13:05:00', NULL, 14, 6),
("Trajet parfait, très doux.", 5, 'approved', '2025-01-26 14:00:00', NULL, 14, 7),
("Très bonne ambiance à bord.", 5, 'approved', '2025-01-26 14:05:00', NULL, 14, 7),
("Une conduite souple et sécurisante.", 5, 'approved', '2025-01-27 15:00:00', NULL, 14, 8),
("Arrivé à destination plus tôt que prévu.", 5, 'approved', '2025-01-27 15:05:00', NULL, 14, 8),
("Tout simplement parfait.", 5, 'approved', '2025-01-28 16:00:00', NULL, 14, 9),
("Un grand merci au conducteur !", 5, 'approved', '2025-01-28 16:05:00', NULL, 14, 9),
("Conduite exemplaire, je me suis senti en sécurité tout le long.", 5, 'approved', '2025-01-29 17:00:00', NULL, 14, 10),
("Personne très agréable et attentive aux passagers.", 5, 'approved', '2025-01-29 17:05:00', NULL, 14, 10),
("La voiture était confortable et l'itinéraire bien choisi.", 5, 'approved', '2025-01-30 18:00:00', NULL, 14, 11),
("Ponctualité irréprochable et excellent sens du service.", 5, 'approved', '2025-01-30 18:05:00', NULL, 14, 11),

-- Reviews passenger 15 (Diana Miller)
("À l'heure, excellent !", 5, 'approved', '2025-02-01 09:00:00', NULL, 15, 2),
("Voyage super agréable, confortable et sûr. Je recommande vivement.", 5, 'approved', '2025-02-01 09:05:00', NULL, 15, 2),
("Trajet fluide, le conducteur était très poli et la voiture propre.", 5, 'approved', '2025-02-02 10:00:00', NULL, 15, 3),
("Expérience plaisante, arrivé à l'heure et la conversation était intéressante.", 5, 'approved', '2025-02-02 10:05:00', NULL, 15, 3),
("Conducteur fantastique, a rendu le voyage agréable.", 5, 'approved', '2025-02-03 11:00:00', NULL, 15, 4),
("Toujours fiable et professionnel.", 5, 'approved', '2025-02-03 11:05:00', NULL, 15, 4),
("Service de première classe, rien à redire !", 5, 'approved', '2025-02-04 12:00:00', NULL, 15, 5),
("J'ai eu froid pendant tout le trajet, la climatisation était trop forte.", 2, 'approved', '2025-02-04 12:05:00', NULL, 15, 5),
("Super sympa et serviable.", 5, 'approved', '2025-02-05 13:00:00', NULL, 15, 6),
("Voyage confortable et efficace.", 5, 'approved', '2025-02-05 13:05:00', NULL, 15, 6),
("Trajet parfait, très doux.", 5, 'approved', '2025-02-06 14:00:00', NULL, 15, 7),
("Très bonne ambiance à bord.", 5, 'approved', '2025-02-06 14:05:00', NULL, 15, 7),
("Une conduite souple et sécurisante.", 5, 'approved', '2025-02-07 15:00:00', NULL, 15, 8),
("Le conducteur n'a pas respecté l'itinéraire prévu.", 2, 'approved', '2025-02-07 15:05:00', NULL, 15, 8),
("Tout simplement parfait.", 5, 'approved', '2025-02-08 16:00:00', NULL, 15, 9),
("Un grand merci au conducteur !", 5, 'approved', '2025-02-08 16:05:00', NULL, 15, 9),
("Conduite exemplaire, je me suis senti en sécurité tout le long.", 5, 'approved', '2025-02-09 17:00:00', NULL, 15, 10),
("Personne très agréable et attentive aux passagers.", 5, 'approved', '2025-02-09 17:05:00', NULL, 15, 10),
("La voiture était confortable et l'itinéraire bien choisi.", 5, 'approved', '2025-02-10 18:00:00', NULL, 15, 11),
("Ponctualité irréprochable et excellent sens du service.", 5, 'approved', '2025-02-10 18:05:00', NULL, 15, 11),

-- Reviews passenger 16 (Eve Wilson)
("À l'heure, excellent !", 5, 'approved', '2025-02-11 09:00:00', NULL, 16, 2),
("Voyage super agréable, confortable et sûr. Je recommande vivement.", 5, 'approved', '2025-02-11 09:05:00', NULL, 16, 2),
("Trajet fluide, le conducteur était très poli et la voiture propre.", 5, 'approved', '2025-02-12 10:00:00', NULL, 16, 3),
("Expérience plaisante, arrivé à l'heure et la conversation était intéressante.", 5, 'approved', '2025-02-12 10:05:00', NULL, 16, 3),
("La conversation était difficile, le conducteur n'était pas très ouvert.", 2, 'approved', '2025-02-13 11:00:00', NULL, 16, 4),
("Toujours fiable et professionnel.", 5, 'approved', '2025-02-13 11:05:00', NULL, 16, 4),
("Service de première classe, rien à redire !", 5, 'approved', '2025-02-14 12:00:00', NULL, 16, 5),
("Super sympa et serviable.", 5, 'approved', '2025-02-14 12:05:00', NULL, 16, 5),
("Voyage confortable et efficace.", 5, 'approved', '2025-02-15 13:00:00', NULL, 16, 6),
("Trajet parfait, très doux.", 5, 'approved', '2025-02-15 13:05:00', NULL, 16, 6),
("Très bonne ambiance à bord.", 5, 'approved', '2025-02-16 14:00:00', NULL, 16, 7),
("Une conduite souple et sécurisante.", 5, 'approved', '2025-02-16 14:05:00', NULL, 16, 7),
("Arrivé à destination plus tôt que prévu.", 5, 'approved', '2025-02-17 15:00:00', NULL, 16, 8),
("A eu du mal à trouver le point de rendez-vous.", 2, 'approved', '2025-02-17 15:05:00', NULL, 16, 8),
("Tout simplement parfait.", 5, 'approved', '2025-02-18 16:00:00', NULL, 16, 9),
("Un grand merci au conducteur !", 5, 'approved', '2025-02-18 16:05:00', NULL, 16, 9),
("Conduite exemplaire, je me suis senti en sécurité tout le long.", 5, 'approved', '2025-02-19 17:00:00', NULL, 16, 10),
("Personne très agréable et attentive aux passagers.", 5, 'approved', '2025-02-19 17:05:00', NULL, 16, 10),
("La voiture était confortable et l'itinéraire bien choisi.", 5, 'approved', '2025-02-20 18:00:00', NULL, 16, 11),
("Le siège passager était cassé.", 2, 'approved', '2025-02-20 18:05:00', NULL, 16, 11),

-- Reviews passenger 17 (Frank Moore)
("À l'heure, excellent !", 5, 'approved', '2025-02-21 09:00:00', NULL, 17, 2),
("Voyage super agréable, confortable et sûr. Je recommande vivement.", 5, 'approved', '2025-02-21 09:05:00', NULL, 17, 2),
("Le conducteur était en retard et la voiture n'était pas très propre.", 2, 'approved', '2025-02-22 10:00:00', NULL, 17, 3),
("Expérience plaisante, arrivé à l'heure et la conversation était intéressante.", 5, 'approved', '2025-02-22 10:05:00', NULL, 17, 3),
("Conducteur fantastique, a rendu le voyage agréable.", 5, 'approved', '2025-02-23 11:00:00', NULL, 17, 4),
("Toujours fiable et professionnel.", 5, 'approved', '2025-02-23 11:05:00', NULL, 17, 4),
("Service de première classe, rien à redire !", 5, 'approved', '2025-02-24 12:00:00', NULL, 17, 5),
("Super sympa et serviable.", 5, 'approved', '2025-02-24 12:05:00', NULL, 17, 5),
("Voyage confortable et efficace.", 5, 'approved', '2025-02-25 13:00:00', NULL, 17, 6),
("Trajet parfait, très doux.", 5, 'approved', '2025-02-25 13:05:00', NULL, 17, 6),
("Très bonne ambiance à bord.", 5, 'approved', '2025-02-26 14:00:00', NULL, 17, 7),
("Une conduite souple et sécurisante.", 5, 'approved', '2025-02-26 14:05:00', NULL, 17, 7),
("Arrivé à destination plus tôt que prévu.", 5, 'approved', '2025-02-27 15:00:00', NULL, 17, 8),
("Tout simplement parfait.", 5, 'approved', '2025-02-27 15:05:00', NULL, 17, 8),
("Un grand merci au conducteur !", 5, 'approved', '2025-02-28 16:00:00', NULL, 17, 9),
("Conduite exemplaire, je me suis senti en sécurité tout le long.", 5, 'approved', '2025-02-28 16:05:00', NULL, 17, 9),
("Personne très agréable et attentive aux passagers.", 5, 'approved', '2025-03-01 17:00:00', NULL, 17, 10),
("La voiture était confortable et l'itinéraire bien choisi.", 5, 'approved', '2025-03-01 17:05:00', NULL, 17, 10),
("La musique était trop forte et le trajet n'était pas confortable.", 2, 'approved', '2025-03-02 18:00:00', NULL, 17, 11),
("Ponctualité irréprochable et excellent sens du service.", 5, 'approved', '2025-03-02 18:05:00', NULL, 17, 11),

-- Reviews passenger 18 (Grace Taylor)
("À l'heure, excellent !", 5, 'approved', '2025-03-03 09:00:00', NULL, 18, 2),
("Voyage super agréable, confortable et sûr. Je recommande vivement.", 5, 'approved', '2025-03-03 09:05:00', NULL, 18, 2),
("Trajet fluide, le conducteur était très poli et la voiture propre.", 5, 'approved', '2025-03-04 10:00:00', NULL, 18, 3),
("Conduite brusque, le conducteur semblait distrait.", 2, 'approved', '2025-03-04 10:05:00', NULL, 18, 3),
("Conducteur fantastique, a rendu le voyage agréable.", 5, 'approved', '2025-03-05 11:00:00', NULL, 18, 4),
("Toujours fiable et professionnel.", 5, 'approved', '2025-03-05 11:05:00', NULL, 18, 4),
("Service de première classe, rien à redire !", 5, 'approved', '2025-03-06 12:00:00', NULL, 18, 5),
("Super sympa et serviable.", 5, 'approved', '2025-03-06 12:05:00', NULL, 18, 5),
("Voyage confortable et efficace.", 5, 'approved', '2025-03-07 13:00:00', NULL, 18, 6),
("Trajet parfait, très doux.", 5, 'approved', '2025-03-07 13:05:00', NULL, 18, 6),
("Très bonne ambiance à bord.", 5, 'approved', '2025-03-08 14:00:00', NULL, 18, 7),
("Une conduite souple et sécurisante.", 5, 'approved', '2025-03-08 14:05:00', NULL, 18, 7),
("Arrivé à destination plus tôt que prévu.", 5, 'approved', '2025-03-09 15:00:00', NULL, 18, 8),
("La voiture sentait le tabac, ce n'était pas agréable.", 2, 'approved', '2025-03-09 15:05:00', NULL, 18, 8),
("Tout simplement parfait.", 5, 'approved', '2025-03-10 16:00:00', NULL, 18, 9),
("Un grand merci au conducteur !", 5, 'approved', '2025-03-10 16:05:00', NULL, 18, 9),
("Conduite exemplaire, je me suis senti en sécurité tout le long.", 5, 'approved', '2025-03-11 17:00:00', NULL, 18, 10),
("Personne très agréable et attentive aux passagers.", 5, 'approved', '2025-03-11 17:05:00', NULL, 18, 10),
("La voiture était confortable et l'itinéraire bien choisi.", 5, 'approved', '2025-03-12 18:00:00', NULL, 18, 11),
("Ponctualité irréprochable et excellent sens du service.", 5, 'approved', '2025-03-12 18:05:00', NULL, 18, 11),

-- Reviews passenger 19 (Henry Anderson)
("À l'heure, excellent !", 5, 'approved', '2025-03-13 09:00:00', NULL, 19, 2),
("Voyage super agréable, confortable et sûr. Je recommande vivement.", 5, 'approved', '2025-03-13 09:05:00', NULL, 19, 2),
("Trajet fluide, le conducteur était très poli et la voiture propre.", 5, 'approved', '2025-03-14 10:00:00', NULL, 19, 3),
("Pas une super expérience, le conducteur était impoli.", 2, 'approved', '2025-03-14 10:05:00', NULL, 19, 3),
("Conducteur fantastique, a rendu le voyage agréable.", 5, 'approved', '2025-03-15 11:00:00', NULL, 19, 4),
("Toujours fiable et professionnel.", 5, 'approved', '2025-03-15 11:05:00', NULL, 19, 4),
("Service de première classe, rien à redire !", 5, 'approved', '2025-03-16 12:00:00', NULL, 19, 5),
("J'ai eu froid pendant tout le trajet, la climatisation était trop forte.", 2, 'approved', '2025-03-16 12:05:00', NULL, 19, 5),
("Super sympa et serviable.", 5, 'approved', '2025-03-17 13:00:00', NULL, 19, 6),
("Voyage confortable et efficace.", 5, 'approved', '2025-03-17 13:05:00', NULL, 19, 6),
("Trajet parfait, très doux.", 5, 'approved', '2025-03-18 14:00:00', NULL, 19, 7),
("Très bonne ambiance à bord.", 5, 'approved', '2025-03-18 14:05:00', NULL, 19, 7),
("Une conduite souple et sécurisante.", 5, 'approved', '2025-03-19 15:00:00', NULL, 19, 8),
("Le conducteur n'a pas respecté l'itinéraire prévu.", 2, 'approved', '2025-03-19 15:05:00', NULL, 19, 8),
("Tout simplement parfait.", 5, 'approved', '2025-03-20 16:00:00', NULL, 19, 9),
("Un grand merci au conducteur !", 5, 'approved', '2025-03-20 16:05:00', NULL, 19, 9),
("Conduite exemplaire, je me suis senti en sécurité tout le long.", 5, 'approved', '2025-03-21 17:00:00', NULL, 19, 10),
("Personne très agréable et attentive aux passagers.", 5, 'approved', '2025-03-21 17:05:00', NULL, 19, 10),
("La voiture était confortable et l'itinéraire bien choisi.", 5, 'approved', '2025-03-22 18:00:00', NULL, 19, 11),
("Ponctualité irréprochable et excellent sens du service.", 5, 'approved', '2025-03-22 18:05:00', NULL, 19, 11),

-- Reviews passenger 20 (Ivy Thomas)
("À l'heure, excellent !", 5, 'approved', '2025-03-23 09:00:00', NULL, 20, 2),
("Voyage super agréable, confortable et sûr. Je recommande vivement.", 5, 'approved', '2025-03-23 09:05:00', NULL, 20, 2),
("Trajet fluide, le conducteur était très poli et la voiture propre.", 5, 'approved', '2025-03-24 10:00:00', NULL, 20, 3),
("Expérience plaisante, arrivé à l'heure et la conversation était intéressante.", 5, 'approved', '2025-03-24 10:05:00', NULL, 20, 3),
("Conducteur fantastique, a rendu le voyage agréable.", 5, 'approved', '2025-03-25 11:00:00', NULL, 20, 4),
("La conversation était difficile, le conducteur n'était pas très ouvert.", 2, 'approved', '2025-03-25 11:05:00', NULL, 20, 4),
("Service de première classe, rien à redire !", 5, 'approved', '2025-03-26 12:00:00', NULL, 20, 5),
("Super sympa et serviable.", 5, 'approved', '2025-03-26 12:05:00', NULL, 20, 5),
("Voyage confortable et efficace.", 5, 'approved', '2025-03-27 13:00:00', NULL, 20, 6),
("Trajet parfait, très doux.", 5, 'approved', '2025-03-27 13:05:00', NULL, 20, 6),
("Très bonne ambiance à bord.", 5, 'approved', '2025-03-28 14:00:00', NULL, 20, 7),
("Une conduite souple et sécurisante.", 5, 'approved', '2025-03-28 14:05:00', NULL, 20, 7),
("Arrivé à destination plus tôt que prévu.", 5, 'approved', '2025-03-29 15:00:00', NULL, 20, 8),
("A eu du mal à trouver le point de rendez-vous.", 2, 'approved', '2025-03-29 15:05:00', NULL, 20, 8),
("Tout simplement parfait.", 5, 'approved', '2025-03-30 16:00:00', NULL, 20, 9),
("Un grand merci au conducteur !", 5, 'approved', '2025-03-30 16:05:00', NULL, 20, 9),
("Conduite exemplaire, je me suis senti en sécurité tout le long.", 5, 'approved', '2025-03-31 17:00:00', NULL, 20, 10),
("Personne très agréable et attentive aux passagers.", 5, 'approved', '2025-03-31 17:05:00', NULL, 20, 10),
("La voiture était confortable et l'itinéraire bien choisi.", 5, 'approved', '2025-04-01 18:00:00', NULL, 20, 11),
("Le siège passager était cassé.", 2, 'approved', '2025-04-01 18:05:00', NULL, 20, 11),

-- Reviews passenger 21 (Jack Jackson)
("À l'heure, excellent !", 5, 'approved', '2025-04-02 09:00:00', NULL, 21, 2),
("Le conducteur était en retard et la voiture n'était pas très propre.", 2, 'approved', '2025-04-02 09:05:00', NULL, 21, 2),
("Trajet fluide, le conducteur était très poli et la voiture propre.", 5, 'approved', '2025-04-03 10:00:00', NULL, 21, 3),
("Expérience plaisante, arrivé à l'heure et la conversation était intéressante.", 5, 'approved', '2025-04-03 10:05:00', NULL, 21, 3),
("Conducteur fantastique, a rendu le voyage agréable.", 5, 'approved', '2025-04-04 11:00:00', NULL, 21, 4),
("Toujours fiable et professionnel.", 5, 'approved', '2025-04-04 11:05:00', NULL, 21, 4),
("Service de première classe, rien à redire !", 5, 'approved', '2025-04-05 12:00:00', NULL, 21, 5),
("Super sympa et serviable.", 5, 'approved', '2025-04-05 12:05:00', NULL, 21, 5),
("Voyage confortable et efficace.", 5, 'approved', '2025-04-06 13:00:00', NULL, 21, 6),
("Trajet parfait, très doux.", 5, 'approved', '2025-04-06 13:05:00', NULL, 21, 6),
("Très bonne ambiance à bord.", 5, 'approved', '2025-04-07 14:00:00', NULL, 21, 7),
("Une conduite souple et sécurisante.", 5, 'approved', '2025-04-07 14:05:00', NULL, 21, 7),
("Arrivé à destination plus tôt que prévu.", 5, 'approved', '2025-04-08 15:00:00', NULL, 21, 8),
("Tout simplement parfait.", 5, 'approved', '2025-04-08 15:05:00', NULL, 21, 8),
("Un grand merci au conducteur !", 5, 'approved', '2025-04-09 16:00:00', NULL, 21, 9),
("Conduite exemplaire, je me suis senti en sécurité tout le long.", 5, 'approved', '2025-04-09 16:05:00', NULL, 21, 9),
("Personne très agréable et attentive aux passagers.", 5, 'approved', '2025-04-10 17:00:00', NULL, 21, 10),
("La voiture était confortable et l'itinéraire bien choisi.", 5, 'approved', '2025-04-10 17:05:00', NULL, 21, 10),
("La musique était trop forte et le trajet n'était pas confortable.", 2, 'approved', '2025-04-11 18:00:00', NULL, 21, 11),
("Ponctualité irréprochable et excellent sens du service.", 5, 'approved', '2025-04-11 18:05:00', NULL, 21, 11);

SET FOREIGN_KEY_CHECKS = 1;