/*
 * カラム「category」を追加する
 */
ALTER TABLE Products
    ADD category varchar(3) NOT NULL
    AFTER price
    ;
