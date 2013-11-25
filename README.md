Bazalt CMS (REST server)
========================
[![Build Status](https://travis-ci.org/esvit/bazalt-api.png)](https://travis-ci.org/esvit/bazalt-api) [![Coverage Status](https://coveralls.io/repos/esvit/bazalt-api/badge.png)](https://coveralls.io/r/esvit/bazalt-api)

* Copyright: (c) 2013 Equalteam
* License: http://www.opensource.org/licenses/mit-license.php

### Dependencies

| Library                                                    | Status                                                                                                         | Coverage                                                                                                                      |
| ---------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------:| -----------------------------------------------------------------------------------------------------------------------------:|
| [Bazalt ORM](https://github.com/esvit/bazalt-orm)          | [![Build Status](https://travis-ci.org/esvit/bazalt-orm.png)](https://travis-ci.org/esvit/bazalt-orm)          | [![Coverage Status](https://coveralls.io/repos/esvit/bazalt-orm/badge.png)](https://coveralls.io/r/esvit/bazalt-orm)          |
| [Bazalt Data](https://github.com/esvit/bazalt-data)        | [![Build Status](https://travis-ci.org/esvit/bazalt-data.png)](https://travis-ci.org/esvit/bazalt-data)        | [![Coverage Status](https://coveralls.io/repos/esvit/bazalt-data/badge.png)](https://coveralls.io/r/esvit/bazalt-data)        |
| [Bazalt Site](https://github.com/esvit/bazalt-site)        | [![Build Status](https://travis-ci.org/esvit/bazalt-site.png)](https://travis-ci.org/esvit/bazalt-site)        | [![Coverage Status](https://coveralls.io/repos/esvit/bazalt-site/badge.png)](https://coveralls.io/r/esvit/bazalt-site)        |
| [Bazalt Session](https://github.com/esvit/bazalt-session)  | [![Build Status](https://travis-ci.org/esvit/bazalt-session.png)](https://travis-ci.org/esvit/bazalt-session)  | [![Coverage Status](https://coveralls.io/repos/esvit/bazalt-session/badge.png)](https://coveralls.io/r/esvit/bazalt-session)  |
| [Bazalt Auth](https://github.com/esvit/bazalt-auth)        | [![Build Status](https://travis-ci.org/esvit/bazalt-auth.png)](https://travis-ci.org/esvit/bazalt-auth)        | [![Coverage Status](https://coveralls.io/repos/esvit/bazalt-auth/badge.png)](https://coveralls.io/r/esvit/bazalt-auth)        |
| [Bazalt REST](https://github.com/esvit/bazalt-rest)        | [![Build Status](https://travis-ci.org/esvit/bazalt-rest.png)](https://travis-ci.org/esvit/bazalt-rest)        | [![Coverage Status](https://coveralls.io/repos/esvit/bazalt-rest/badge.png)](https://coveralls.io/r/esvit/bazalt-rest)        |

### ElasticSearch Nginx config

```
location /search/ {
    if ($request_filename ~ "_shutdown") {
        return 403;
        break;
    }

    set $denied_method 0;
    if ($request_method ~ "GET") {
        set $denied_method 1;
    }
    if ($request_method ~ "OPTIONS") {
        set $denied_method 1;
    }
    if ($request_method ~ "POST") {
        set $denied_method 1;
    }
    if ($request_filename ~ "_cluster") {
        return 403;
        break;
    }
    if ($denied_method = 0) {
        return 403;
        break;
    }

    proxy_pass_header Access-Control-Allow-Origin;
    proxy_pass_header Access-Control-Allow-Methods;
    proxy_hide_header Access-Control-Allow-Headers;

    add_header Access-Control-Allow-Headers 'X-Requested-With, Content-Type';
    add_header Access-Control-Allow-Credentials true;

    proxy_pass http://localhost:9210;
    proxy_redirect off;

    proxy_set_header  X-Real-IP  $remote_addr;
    proxy_set_header  X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header  Host $http_host;
}
```