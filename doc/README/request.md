# Project 7 - Create a web service exposing an API

This project was realized for my PHP/Symfony developer training at Openclassrooms.

## Context
BileMo is a company offering a wide selection of high-end cell phones.

You are in charge of the development of BileMo's cell phone showcase. BileMo's business model is not to sell its products directly on the website, but to provide all the platforms that wish to access the catalog via an API (Application Programming Interface). It is therefore exclusively a B2B (business to business) sale.

You will have to expose a certain number of APIs so that the applications of other web platforms can perform operations.

## Customer need
The first customer has finally signed a partnership contract with BileMo! It's a real rush to meet the needs of this first customer, which will allow us to set up all the APIs and to test them immediately.

After a dense meeting with the customer, a certain amount of information was identified. It must be possible to :

- consult the list of BileMo products;
- consult the details of a BileMo product;
- consult the list of registered users linked to a customer on the website;
- consult the details of a registered user linked to a customer;
- add a new user linked to a customer;
- delete a user added by a client.

Only referenced clients can access the APIs. API clients must be authenticated via OAuth or JWT.

## Data format
The first BileMo partner is very demanding: it requires you to expose your data following the rules of levels 1, 2 and 3 of the Richardson model. He has requested that you serve the data in JSON. If possible, the client wants the responses to be cached in order to optimize the performance of requests to the API.