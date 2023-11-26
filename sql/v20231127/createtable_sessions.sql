/**
 * セッション管理用テーブル
 *
 */
CREATE TABLE Sessions (
    id         varchar(44) NOT NULL,  -- セッションID（SESS+文字列）
    user_id    int,                   -- ユーザーID
    expired    int         NOT NULL,  -- 有効期限（UNIXタイム）
    regist_at  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id)
);