<?php
declare(strict_types=1);
use DI\ContainerBuilder;
return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        "settings" => [
            "databases" => [
                "proyecto" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "proyecto",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "proyectos" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "proyectos",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "rad" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "rad",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "maestra" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "maestra",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "promo" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "promo",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "cliente" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "cliente",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "log" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "log",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "cfg" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "cfg",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "sc" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "sc",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "chat" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "chat",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "usuario" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "usuario",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "lvl_rad" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "lvl_rad",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "4378" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "4378",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "8822" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "8822",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "21212" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "21212",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "22985" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "22985",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "40004" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "40004",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "40404" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "40404",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "50505" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "50505",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "53033" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "53033",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "55512" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "55512",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "56835" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "56835",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "65222" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "65222",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "65223" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "65223",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "75969" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "75969",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "78425" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "78425",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "78426" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "78426",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "84282" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "84282",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],

                "88236" => [
                    "driver" => "pdo_mysql",
                    "host" => "localhost",
                    "dbname" => "88236",
                    "user" => "gtp-web-dbg01",
                    "password" => "206Zd542czA4",
                    "charset" => "utf8",
                ],
            ],
        ],
    ]);
};
?>
