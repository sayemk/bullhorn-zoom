CREATE TABLE `contacts` (
                            `bullhorn_id` bigint NOT NULL,
                            `phone` varchar(100) DEFAULT NULL,
                            `email` varchar(100) DEFAULT NULL,
                            `firstName` varchar(100) DEFAULT NULL,
                            `lastName` varchar(100) DEFAULT NULL,
                            `phone2` varchar(100) DEFAULT NULL,
                            `phone3` varchar(100) DEFAULT NULL,
                            `mobile` varchar(100) DEFAULT NULL,
                            `zoom_contact_id` varchar(100) DEFAULT NULL,
                            PRIMARY KEY (`bullhorn_id`),
                            KEY `contacts_zoom_contact_id_IDX` (`zoom_contact_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;