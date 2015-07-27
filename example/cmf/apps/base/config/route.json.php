{
	"/index/(show[0-9]+)":{"module":"base","controller":"index","action":1},
	"/admin":{"module":"backend","controller":"index","action":"index"},
	"/admin/:controller":{"module":"backend","controller":1,"action":"index"},
	"/admin/:controller/:action/:params":{"module":"backend","controller":1,"action":2,"params":3},
	"/([\\w]+)":{"module":"frontend","controller":"index","action":1}
}