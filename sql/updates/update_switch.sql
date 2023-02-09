ALTER TABLE `account`
    ADD COLUMN `frontend_remark` TEXT NOT NULL,
    ADD COLUMN `message` TEXT NOT NULL,
    ADD COLUMN `enable_check_password_correct` TINYINT(1) NOT NULL,
    ADD COLUMN `enable_delete_devices` TINYINT(1) NOT NULL;