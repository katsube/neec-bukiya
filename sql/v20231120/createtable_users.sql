/**
 * User table
 */
CREATE TABLE Users (
    id         int         AUTO_INCREMENT,
    nickname   varchar(64) NOT NULL,
    loginid    varchar(12) NOT NULL,
    password   varchar(32) NOT NULL,
    status     int         NOT NULL  DEFAULT 0 COMMENT '0:normal, 1:退会, 9:BAN',
    regist_at  datetime    NOT NULL  DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime    NOT NULL  DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id)
);
