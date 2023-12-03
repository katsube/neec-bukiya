/*
 * Inventries table
 *
 * ユーザーの所持アイテム情報（インベントリー）
 */
CREATE TABLE Inventories (
    user_id     int       NOT NULL,
    product_id  int       NOT NULL,
    regist_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    /* 主キーは2つの組み合わせにすることも可 */
    PRIMARY KEY (user_id, product_id)
);