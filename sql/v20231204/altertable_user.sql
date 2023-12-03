/*
 * Usersテーブルに所持金(amount)カラムを追加する
 *
 */
ALTER TABLE Users
    ADD COLUMN amount int NOT NULL DEFAULT 0 COMMENT '所持金'
    AFTER password;
