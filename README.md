# Stock & crypto value prediction app

This is a very simple initial version of the app.

You can try it with `bin/console forecast:stock <base_currency> <stock/crypto code> <historical days to collect> <days to foresee>`

Example: `bin/console stocks:predict USD BTC 60 5` 

This will generate a table with the prevision for the next 5 days based in the last 60 days of historical data.

There is a `forecast:test` command too that allow you to test different predicition strategies with sample sequences.

At this moment it only can use some linear algorithms, SquareLevels, Support Vector Regression and a basic linear regression based on Cumulative Moving Averages.

Next steps: 
* apply something more sophisticated as prediction algorithm, being able to make a complete technical analysis of the stock historical values.
* gather information about the stock from Twitter and financial news, to build a sentiment graph around the stock, to affect the predictions.
* apply some ML to find patterns between the stock value evolution and other stocks in the same segment, to affect the predictions.
* ...¿?
