Local install instructions:
```shell
git clone git@github.com:13DaGGeR/trengo_test.git \
&& cd trengo_test \
&& cp .env.example .env \
&& docker run --rm -u "$(id -u):$(id -g)" -v $(pwd):/opt -w /opt laravelsail/php81-composer:latest composer install --ignore-platform-reqs \
&& ./vendor/bin/sail up -d --build \
&& ./vendor/bin/sail artisan migrate \
&& ./vendor/bin/sail artisan db:seed \
&& ./vendor/bin/sail artisan test
```

Workers (not needed to run tests):
```
./artisan queue:work redis --queue=views
```

## API methods: 

### get list of categories
`GET /api/categories`
##### response example:
```json
{
    "data": [
        {
            "id": 1,
            "title": "Category name"
        }
    ]
}
```

### get article
`GET /api/articles/{id}`
##### response example:
```json
{
  "data": {
    "id": 1,
    "title": "et laborum itaque",
    "body": "Non illum sed consequatur nihil. Eligendi et magnam optio velit aut.",
    "created_at": "2022-06-13 18:23:06",
    "rating": 4.9999,
    "categories": [
      {
        "id": 1,
        "title": "Category name"
      }
    ]
  }
}
```

### create article
`POST /api/articles/`

parameters:
* `title` string, required, max 255 chars
* `body` string, required, max 10000 chars
* `categories` int[], optional, array of ids of categories

##### response examples:
```json
{
    "id": 1001
}
```
```json
{
  "message": "The title field is required.",
  "errors": {
    "title": [
      "The title field is required."
    ]
  }
}
```

### rate article
`POST /api/ratings/`

parameters:
* `article_id` int, required
* `value` int, rate value [1,5]

response result is empty

### get list of articles
`GET /api/articles/`

all parameters are optional

parameters:
* `page` integer [1, 1000]
* `page_size` integer [1, 100]
* `date_from` string in format of YYYY-MM-DD
* `date_to` string in format of YYYY-MM-DD
* `categories` string - id of categories, separated by comma, ex `categories=1,2,3`
* `q` string - search request, fuzzy search will be performed by text and body of the articles
* `sort` string - could be either `rating` or `views`. If `sort=views` requested, `trending_date` can be provided
* `trending_date` string in format of YYYY-MM-DD - minimal date to count views if `sort=views` requested 

##### response example:
```json
{
    "items": [
        {
            "id": 560,
            "title": "et laborum itaque",
            "body": "Non illum sed consequatur nihil. Eligendi et magnam optio velit aut.",
            "created_at": "2022-06-13 18:23:06",
            "rating": 3.0937,
            "categories": [
                {
                    "id": 9,
                    "title": "atque"
                }
            ]
        }
    ],
    "page": 1,
    "page_size": 1,
    "total": 1000
}
```

#### A note about choices =)
Some approaches may seem strange or unreasonable, so I would like to explain my thinking process.

I see sorting "by views" by an arbitrary period as the main performance bottleneck.
As writing new views performed on every article read request, I decided that delaying the writing needed to provide content faster.
I avoided locking unique pairs of "ip-article" of views in redis, because if these processed in a queue, locking does not have much sense.
The denormalization of the views table is needed to speed up article list requests with the "by views" sorting.
My checks showed that requests with joining the `article_views` table are 2-3 times faster than joining the `ip_views`.

Elasticsearch used in a somewhat incomplete way: the ability to sort results is ignored.
This is because I did not find the way to implement sorting by article views for a given date inside elasticsearch.
Using sorting inside elasticsearch only for rating search seems unreasonable for me: filtering logic will be duplicated in code.
