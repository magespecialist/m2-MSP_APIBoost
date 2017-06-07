# MSP APIBoost

A quick and dirty module to create a cache layer for frontend REST applications.

## Why you need it?

While using Magento 2 REST API for decoupled frontend (e.g.: a ReactJS or AngularJS frontends), you may need to access catalog, search and products serveral times.

Magento FPC or Varnish could not be the best options you have to cache API requests.
This is a small customizable caching mechanism with pluggable rules via di.xml .

With this simple module you will be able to cache API REST requests and get up to a 50x performance boost.
