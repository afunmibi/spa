-- Alter enrolment table to make staff_id nullable
ALTER TABLE `enrolment` MODIFY COLUMN `staff_id` VARCHAR(64) DEFAULT NULL COMMENT 'ID of staff member who processed the enrolment';
