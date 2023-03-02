ALTER TABLE `share`
    ADD COLUMN `remark` VARCHAR(128);
ALTER TABLE `account`
    ADD COLUMN `enable_auto_update_password` TINYINT(1) NOT NULL;