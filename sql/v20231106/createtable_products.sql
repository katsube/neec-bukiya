CREATE TABLE Products (
    id          int,
    name        varchar(255) NOT NULL,
    price       int          NOT NULL,
    image       varchar(255) DEFAULT NULL,
    description varchar(255) DEFAULT NULL,
    regist_date datetime     NOT NULL,
    update_date datetime     NOT NULL,

    PRIMARY KEY (id)
);
