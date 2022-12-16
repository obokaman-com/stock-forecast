# Stock & crypto value prediction app

This is a very simple initial version of the app.

At this moment it only can use some linear algorithms, SquareLevels, Support Vector Regression and a basic linear regression based on Cumulative Moving Averages.

Next steps: 
* apply something more sophisticated as prediction algorithm, being able to make a complete technical analysis of the stock historical values.
* gather information about the stock from Twitter and financial news, to build a sentiment graph around the stock, to affect the predictions.
* apply some ML to find patterns between the stock value evolution and other stocks in the same segment, to affect the predictions.
* ...¿?

## Installation

**Install PHP 8.1 and Git in your local machine** *(Example for Mac)*

Install Homebrew (package manager) if you don't have it yet, and install Git + PHP 8.1 + Composer:

```bash
$ /usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)`
$ brew install git php composer
```

**Download app and install all dependencies**

```bash
$ git clone https://github.com/obokaman-com/stock-forecast
$ cd stock-forecast
$ composer install
```

#### API keys

This code uses the CryptoCompare API, for large number of queries you will need to register an API key at http://www.cryptocompare.com and set it in `.env`.

Similarly, you will need to set a Telegram bot key in .env to use the Telegram functionality.


## Examples

#### Forecast  

You can try it with `bin/console forecast:stock <base_currency> <stock/crypto code> <date_interval_magnitude (minutes/hours/days)>`

```bash
$ bin/console forecast:stock USD BTC days
``` 

This will generate a table with the historical price information and a prevision for the next period based in the historical data, giving you three estimations, 
based on short-term, medium-term and long-term.

**Output:**

```
===== BUILDING FORECAST FOR BTC - USD USING DATA FROM LAST 30 days =====

Last real measurements:
+------------------+----------+----------+----------+------------+----------+----------+------------+---------------+
| Date             | Open     | Close    | Change   | Change (%) | High     | Low      | Volatility | Volume        |
+------------------+----------+----------+----------+------------+----------+----------+------------+---------------+
| 2017-11-15 00:00 | 6597.06  | 7283.22  | 686.16   | 10.4%      | 7330.06  | 6596.94  | 733.12     | 922828348.24  |
| 2017-11-16 00:00 | 7283.02  | 7853.68  | 570.66   | 7.84%      | 7964.64  | 7119.17  | 845.47     | 1009996825.41 |
| ------...------- | --...--- | --...--- | ---...-- | ----...--- | ---...-- | ---...-- | ----...--- | -----...----- |
| 2017-12-13 00:00 | 17083.9  | 16286.82 | -797.08  | -4.67%     | 17267.96 | 15669.86 | 1598.1     | 2575900534.34 |
| 2017-12-14 00:00 | 16286.82 | 16459.79 | 172.97   | 1.06%      | 16941.08 | 16023.64 | 917.44     | 1744832688.08 |
+------------------+----------+----------+----------+------------+----------+----------+------------+---------------+

Forecast for next days:
+---------------+----------+----------+---------+------------+----------+----------+------------+---------------+
| Date interval | Open     | Close    | Change  | Change (%) | High     | Low      | Volatility | Volume        |
+---------------+----------+----------+---------+------------+----------+----------+------------+---------------+
| Short term    | 16459.79 | 16014.7  | -445.09 | -2.7%      | 17645.78 | 17189.89 | 455.88     | 1708072344.68 |
| Medium term   | 16459.79 | 16648.77 | 188.98  | 1.15%      | 19085.95 | 16795.52 | 2290.42    | 3094114369.44 |
| Long term     | 16459.79 | 16886.1  | 426.31  | 2.59%      | 17608.83 | 15276.44 | 2332.38    | 2923450650.25 |
+---------------+----------+----------+---------+------------+----------+----------+------------+---------------+

```

#### Insights & signals  

You can try it with `bin/console forecast:signal <base_currency> <stock/crypto code> -t (optional for including forecast table)`

```bash
$ bin/console forecast:signals EUR ETC
``` 

This will output some signals and score built based on last month / day / hour for given currency / crypto pair, or the default pairs if
none are given. 

**Output:**

```
===== SOME SIGNALS FOR EUR - ETC ON Jan 14th, 15:21h =====

Signals based on last hour (Score: 0):
 - Stable in this period.

Signals based on last day (Score: -1):
 - Loosing value in short term.

Signals based on last month (Score: 3):
 - Improving exponentially.
 - Recovering value in short term.
 - Having a noticeable improvement recently (6.62%).
```

There is a `forecast:test` command too that allow you to test different predicition strategies with sample sequences.

#### Telegram bot (@CryptoInsightsBot)

You can use this project to run a robo-advisor using Telegram. You'll find some commands and callbacks implemented under `App/Controller/Telegram` 

If you want to test with your own bot, you should add an environment variable called `TELEGRAM_BOT_TOKEN`, with your assigned token. More information about creating your own Telegram bot on https://core.telegram.org/bots 

An example Telegram bot is running as @CryptoInsightsBot. You can test it at https://t.me/CryptoInsightsBot

It implements two simple commands: 
* `/insights`: allow you to view signals for choosen currency - crypto pair
* `/subscribe`: allow you to receive real-time alerts with relevant signals for choosen currency - crypto pair signal.

![Telegram bot](https://raw.githubusercontent.com/obokaman-com/stock-forecast/assets/telegram-bot.gif)
