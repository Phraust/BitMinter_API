BitMinter_API
=============

A quick PHP / Gumby Framework Interface I slapped together for BitMinter's API. Just add in your Key and preferred refresh rate.

Using Gumby Framework 1.1 (http://www.gumbyframework.com/)

Requires

1. Apache2 (or similar webserver)
2. PHP5
3. php5-json
4. php5-curl

How To Setup
============

1.  Install Apache2, PHP5, php5-json and php5-curl (For ubuntu 13.10 use sudo apt-get install apache2 php5 php5-json php5-curl)
2.  Clone the repo to your directory.
    * For me it's cd /srv/http/ then git clone git://github.com/Phraust/BitMinter_API.git, this will put it in http://HOST/BitMinter_API/.
3.  CHMOD the cache file so it's writeable (cd BitMinter_API then chmod 777 cache.txt).
4.  Add your preffered API KEY and Timeout to the configuration portion of index.php.
5.  I think that's pretty much it.
6.  You can see the debug output from variables and arrays present by appending ?Debug=1 to the url (http://HOST/BitMinter_API/index.php?Debug=1).


Anything Else
=============

This is my first git repo, and I have no idea what I'm doing.  Also, I haven't touched PHP for months, so it's probably all garbage.  If you have some suggestions, you can email me or reach me just about anytime on Freenode in #Bitminter.
