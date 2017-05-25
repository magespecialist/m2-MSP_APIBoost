# MSP APIBoost

A quick and dirty module to create a cache layer for frontend REST applications.

## Why you need it?

While using Magento 2 REST API for decoupled frontend (e.g.: a ReactJS or AngularJS frontends), you may need to access catalog, search and products.

Everytime you perform a REST API call, no matter if you have a FPC configured or not, Magento will compute your request as it was the first one.

With this simple module you will be able to cache API REST requests and get up to a 50x performance boost.
