CREATE TABLE Products (
    id          int,
    name        varchar(128) NOT NULL,
    description varchar(255),
    image       varchar(32),
    price       int          NOT NULL,
    regist_date datetime     NOT NULL,
    update_date datetime     NOT NULL,

    PRIMARY KEY (id)
);