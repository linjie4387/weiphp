DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='invite' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='invite' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_invite`;


DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='invite_user' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='invite_user' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_invite_user`;


