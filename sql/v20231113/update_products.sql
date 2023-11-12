/*
 * categoryの値を設定する
 *
 */
UPDATE Products SET category='TWS' WHERE id in (1,2);  -- TWS = Two-Handed Sword
UPDATE Products SET category='GUN' WHERE id = 3;  -- GUN = Gun
UPDATE Products SET category='ARM' WHERE id = 4;  -- ARM = Arm
UPDATE Products SET category='MAG' WHERE id = 5;  -- MAG = Magic
