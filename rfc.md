## Description

chassis would be a back-end framework iam to use for api micro-services based on open source packages.

### Propose solution

- [x] The chassis itself will be written in PHP.
- [ ] It will used an existing components for common task (such as routing the request, logging, database, etc). It will strive to allow replacing those components at a later stage easily (by coding an interface instead to a concrete implementation).
- [ ] It will provide an standard way to add routes.
- [ ] It will provide a middleware layer and an standard way to add a middleware.
- [ ] It will provide a component to validate request using json-validator.
- [ ] It will provide an standard way to organize controllers or actions to manage requests.
- [ ] It will provide an standard way to serialize responses.
- [ ] It will provide a plugin system and an standard way to add a plugin.
- [ ] It will provide a data layer to connect to the database (at the begging will only support Postgres database).
- [ ] It will provide a behavioral test suite

### How will it be tested?

Only unit test will be written. Testing the database layer does not make sense, tests trend to be slow and inefficient along with irrelevant.

