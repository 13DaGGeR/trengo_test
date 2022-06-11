
Workers:
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

