# Magento 2 / IPQualityScore Integration

## About the Project

This was a practical Magento 2 fraud-prevention integration built around IPQualityScore (https://www.ipqualityscore.com).

The goal was to take information already available during customer login and checkout, send it to IPQualityScore for fraud and transaction-risk analysis, and make the returned risk data useful inside the Magento order workflow.

The interesting part for me wasn't simply calling the IPQS API. It was deciding where fraud analysis belonged in the Magento lifecycle, what transaction and customer information should be sent, how returned scores should affect an order, and how that result should be stored and exposed inside Magento.

There are things I would implement differently today, particularly around HTTP handling, logging, testing, request abstraction and IP detection. I've left the original implementation intact and documented those changes below rather than rewriting the project to make an older codebase look newer than it is.

## Technology

* PHP
* Magento 2
* IPQualityScore
* REST APIs
* Magento Event / Observer architecture
* Magento Dependency Injection
* Magento Admin configuration
* Magento extension attributes
* Magento resource models and persistence
* cURL / HTTP integration

## What It Does

The module integrates Magento customer and order activity with IPQualityScore.

It captures information during customer login and payment processing and submits that information to IPQS for risk analysis.

For transaction scoring, the integration can send information such as:

* Customer IP address
* User agent and browser language
* Order amount
* Customer email
* Billing name and address
* Shipping name and address
* Phone information
* Credit-card expiration data
* IPQS fraud-detection configuration options

IPQualityScore returns fraud and transaction-risk information.

The module then uses the returned transaction risk score to determine how the Magento order should be handled.

Depending on configured thresholds, an order can:

* Continue as a legitimate order
* Be placed on hold for review
* Be marked as suspected fraud
* Be canceled
* Have the risk score and decision stored with the order

Automatic order-status changes can also be disabled so the integration can operate in a monitoring or evaluation mode without changing the order workflow.

## Architecture

The integration uses Magento's event/observer architecture rather than modifying the Magento checkout directly.

The basic transaction flow is:

```text
Magento Checkout / Payment
          |
          v
Magento Payment Event
          |
          v
OrderObserver
          |
          v
IPQualityScore API
          |
          v
Fraud Score + Transaction Risk Score
          |
          v
OrderManager
          |
          +---- Legitimate ---> Continue processing
          |
          +---- Review -------> Hold / flag order
          |
          +---- High Risk ----> Flag / cancel order
          |
          v
Persist Risk Score + Decision
```

The module is divided into several primary responsibilities.

**Observers**

Magento observers listen for customer and order events.

The order observer handles payment and order lifecycle events and passes the appropriate Magento order and payment information to the IPQS integration.

Customer observers can also submit login-related information for IP and fraud analysis.

**IPQualityScore API Integration**

The API helper translates Magento customer, address, payment and order information into the parameters expected by IPQualityScore.

It submits the request to IPQS and processes the returned fraud and transaction-risk information.

**Order Management**

The `OrderManager` translates the IPQS transaction risk score into Magento behavior.

Two configurable thresholds determine whether an order should:

* Continue normally
* Be placed into review
* Be treated as a high-risk order

Automatic order updates can be disabled so IPQS results are recorded without changing order status.

**Configuration**

`ConfigSettings` provides access to the Magento configuration used by the integration, including settings such as:

* API enabled / disabled
* IPQualityScore API credentials
* API endpoint
* IPQS strictness settings
* Public-access-point handling
* Fraud-analysis options
* Review threshold
* Cancellation threshold
* Automatic order-status behavior

**Persistence**

IPQS request and response data can be persisted for later review.

Transaction risk scores and decisions are also stored with the Magento order using extension attributes and supporting resource models.

This allows the risk decision to remain associated with the order after the original API request has completed.

## Production Readiness

This repository represents the working integration and the architecture I was exploring at the time.

Before putting this version into a production environment today, I would make several changes.

### TODO

* [ ] Remove development-level order and payment logging and replace it with configurable, structured logging.
* [ ] Make sure customer, transaction and payment information is never unnecessarily written to application logs.
* [ ] Move debug behavior into Magento configuration or environment settings rather than enabling it in code.
* [ ] Replace direct access to PHP request globals with Magento request abstractions.
* [ ] Use trusted-proxy-aware IP detection rather than relying directly on forwarded HTTP headers.
* [ ] Move IPQS HTTP communication into a dedicated injectable API client.
* [ ] Replace direct cURL usage with a testable HTTP-client abstraction.
* [ ] Add explicit handling for HTTP errors, timeouts, malformed responses and IPQS service failures.
* [ ] Add retry and failure behavior appropriate for checkout processing.
* [ ] Separate Magento-to-IPQS parameter mapping from the API client.
* [ ] Replace Magento generated `Interceptor` types in method signatures with stable Magento interfaces or domain types.
* [ ] Validate IPQS responses before reading fraud and transaction-risk fields.
* [ ] Review order cancellation and hold behavior against current Magento order-service APIs.
* [ ] Support missing shipping addresses and virtual orders explicitly.
* [ ] Add PHPUnit tests for legitimate, review, cancellation, missing-data and API-failure scenarios.
* [ ] Add integration tests around Magento order-state transitions.
* [ ] Add tests for login and transaction risk requests.
* [ ] Add static analysis, linting and automated tests to the CI pipeline.
* [ ] Review the module against current Magento and IPQualityScore APIs before deployment.
* [ ] Evaluate newer IPQS decisioning fields and recommended-action data rather than relying only on locally configured numerical thresholds.
