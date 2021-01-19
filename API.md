# API

# List servers

`GET /servers/list`


```javascript
Content-Type: application/json
API-KEY: <API_KEY>

{
	"servers": [{
		"id": 1,
		"host": "atlanta-speedtest.test.com"
	}, {
		"id": 2,
		"host": "atlanta2.speedtest.test.com"
	}, {
		"id": 3,
		"host": "dallas-speedtest.test.com"
	}],
	"location": {
		"city": "Colorado Springs",
		"state": "CO",
		"country": "US",
		"zip": "80923",
		"longitude": -104.8214,
		"latitude": 38.8339
	}
}
```


`GET /api/tcp/server`

```bash
curl -X GET -H "API-KEY: 4501c091b0366d76ea3218b6cfdd8097" https://speedtest.test.com/api/tcp/server
```

Should respond with something like below

```javascript
{"id":3,"host":"dallas-speedtest.test.com","port":8698,"method":"tcp"}
```

## Upload speedtest results

`POST /speedtest/results`

```
Content-Type: application/json
API-KEY: <API_KEY>

{
  "mac":"01:23:45:67:89:ab",
  "results": {
  "upload":"980",
  "download":"980"
  }
}
```