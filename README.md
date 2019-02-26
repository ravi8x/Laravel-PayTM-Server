Laravel PayTM Backend - REST API
===================
Larvel e-commerce project including PayTM payement gateway solution.

Android Demo App
===============
You can refer [Mart 9](https://github.com/ravi8x/Android-PayTM) e-commerce app that uses this project as backend.

![Android Ecommerce PayTM integration](https://www.androidhive.info/wp-content/uploads/2019/02/android-e-commerce-app-paytm-integration.png)

REST API
===================
Base Url: [https://demo.androidhive.info/paytm/public/api/](https://demo.androidhive.info/paytm/public/api/)

Postman collection: [https://www.getpostman.com/collections/8b2e7763a8b7e0673918](https://www.getpostman.com/collections/8b2e7763a8b7e0673918)

|Header|Value|Description|
|----------|--------|------|
|**Authorization**|Bearer A492Kdleo3d83ba21699…|Use the token received in /login or /register call|

|Endpoint|Method|Description|
|----------|--------|------|
|**/appConfig**|GET|PayTM app config like Merchant ID and app environment (dev / production)|
|**/register**|POST|Registering a new user. This returns auth token needed to make further calls|
|**/login**|POST|Login of an existing user. This returns auth token needed to make further calls|
|**/products**|GET|Fetching all products along with name, thumbnail and price|
|**/prepareOrder**|POST|Preparing a new order. This takes list of cart items and gives the unique Order ID that needs to be sent to PayTM|
|**/getChecksum**|POST|Generates the checksum needed while redirecting to PayTM payment screen|
|**/transactionStatus**|POST|Verifies the transaction status once the payment is done. This involves our backend server making call to PayTM server and verifies the transaction|
|**/transactions**|GET|List of transactions made by a user|
|**/orders/{id}**|GET|Complete details of a single order including the total amount and list of items ordered|
