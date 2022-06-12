
Workers (not needed to run tests):
```
./artisan queue:work redis --queue=views
```

## API methods: 

### get list of categories
`GET /api/categories`

### get article
`GET /api/articles/{id}`

### create article
`POST /api/articles/`

parameters:
* `title` string, required, max 255 chars
* `body` string, required, max 10000 chars
* `categories` int[], optional, array of ids of categories

### rate article
`POST /api/ratings/`

parameters:
* `article_id` int, required
* `value` int, rate value [1,5]

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
