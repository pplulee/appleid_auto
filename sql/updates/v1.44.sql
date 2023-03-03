ALTER TABLE `share`
    ADD COLUMN `remark` text;
ALTER TABLE `account`
    ADD COLUMN `enable_auto_update_password` TINYINT(1) NOT NULL;
UPDATE share SET remark="";