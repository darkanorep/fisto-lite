<?php

    namespace App\Http\Response;


    class Status {

        const OK = 200;
        const CREATED = 201;
        const UNAUTHORIZED = 401;
        const FORBIDDEN = 403;
        const NOT_FOUND = 404;
        const CONFLICT = 409;
    }

?>