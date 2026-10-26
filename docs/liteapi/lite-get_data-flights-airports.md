---
updatedAt: 2026-04-24T08:36:00.000Z
agentTools:
  projectIndex: https://docs.liteapi.travel/llms.txt
---

# Search airports

## Overview

Search for airports by name, city, or IATA code using a text query. Returns matching airports for use in autocomplete and search inputs.

## When to Use

- **Airport autocomplete** - Power origin/destination search inputs with type-ahead suggestions
- **Airport discovery** - Find airports in a city or region by name
- **Search validation** - Look up airports before constructing a flight search request

## What You Get

- **Matching airports** ranked by relevance to the query
- **IATA codes** for `legs[].origin` and `legs[].destination` on `POST /flights/rates`
- **City and country** details for display purposes
- **Geographic coordinates** for map-based interfaces

## Quick Start

Provide a `q` query string (minimum 2 characters) to search by airport name, city, or code. Returns matching airports ordered by relevance.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "title": "API: Flights",
    "version": "3.0.0",
    "description": "The Flights API provides endpoints for searching flights, managing bookings, and accessing static flight data including airlines and airports."
  },
  "servers": [
    {
      "url": "https://api.liteapi.travel/v3.0"
    }
  ],
  "security": [
    {
      "apikeyAuth": []
    }
  ],
  "components": {
    "securitySchemes": {
      "apikeyAuth": {
        "name": "X-API-Key",
        "type": "apiKey",
        "in": "header"
      }
    },
    "schemas": {
      "Airport": {
        "type": "object",
        "properties": {
          "city": {
            "type": "string",
            "description": "City name"
          },
          "country": {
            "type": "string",
            "description": "Country name"
          },
          "iata": {
            "type": "string",
            "description": "3-letter IATA airport code"
          },
          "icao": {
            "type": "string",
            "description": "4-letter ICAO airport code"
          },
          "lat": {
            "type": "number",
            "description": "Latitude"
          },
          "lon": {
            "type": "number",
            "description": "Longitude"
          },
          "name": {
            "type": "string",
            "description": "Airport name"
          },
          "state": {
            "type": "string",
            "description": "State or province"
          },
          "tz": {
            "type": "string",
            "description": "Timezone (e.g., America/New_York)"
          }
        },
        "description": "Full airport details including location, codes, and timezone"
      },
      "Error": {
        "type": "object",
        "properties": {
          "error": {
            "type": "object",
            "properties": {
              "code": {
                "type": "integer",
                "description": "Numeric error code"
              },
              "message": {
                "type": "string",
                "description": "Short error message"
              },
              "description": {
                "type": "string",
                "description": "Detailed error description"
              },
              "key": {
                "type": "string",
                "description": "JSON path to the offending request field (e.g. bodyRequest.offerId, query.airlinePnr). Present on validation and client-input errors; omitted on auth and server-side errors.",
                "example": "bodyRequest.offerId"
              }
            },
            "description": "Error details"
          }
        },
        "description": "Standard error response envelope",
        "example": {
          "error": {
            "code": 4000,
            "message": "bad request",
            "description": "invalid flight extra charges: usePaymentSdk must be true for Stripe bookings"
          }
        }
      }
    }
  },
  "tags": [
    {
      "name": "Airports",
      "description": "Retrieve airport information and IATA codes"
    }
  ],
  "paths": {
    "/data/flights/airports": {
      "get": {
        "tags": [
          "Airports"
        ],
        "summary": "Search airports",
        "description": "## Overview\n\nSearch for airports by name, city, or IATA code using a text query. Returns matching airports for use in autocomplete and search inputs.\n\n## When to Use\n\n- **Airport autocomplete** - Power origin/destination search inputs with type-ahead suggestions\n- **Airport discovery** - Find airports in a city or region by name\n- **Search validation** - Look up airports before constructing a flight search request\n\n## What You Get\n\n- **Matching airports** ranked by relevance to the query\n- **IATA codes** for `legs[].origin` and `legs[].destination` on `POST /flights/rates`\n- **City and country** details for display purposes\n- **Geographic coordinates** for map-based interfaces\n\n## Quick Start\n\nProvide a `q` query string (minimum 2 characters) to search by airport name, city, or code. Returns matching airports ordered by relevance.",
        "parameters": [
          {
            "name": "q",
            "in": "query",
            "required": true,
            "schema": {
              "type": "string"
            },
            "description": "Search query (minimum 2 characters, e.g., 'JFK' or 'New York')",
            "example": "JFK"
          }
        ],
        "responses": {
          "200": {
            "description": "Successful response",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "data": {
                      "type": "array",
                      "items": {
                        "type": "object",
                        "properties": {
                          "airports": {
                            "type": "array",
                            "items": {
                              "$ref": "#/components/schemas/Airport"
                            },
                            "description": "List of airports matching the query"
                          },
                          "count": {
                            "type": "integer",
                            "description": "Total number of airports matching the query"
                          }
                        }
                      },
                      "description": "List of airport result sets"
                    }
                  }
                },
                "example": {
                  "data": [
                    {
                      "airports": [
                        {
                          "city": "New York",
                          "country": "United States",
                          "iata": "JFK",
                          "icao": "KJFK",
                          "lat": 40.63980103,
                          "lon": -73.77890015,
                          "name": "John F Kennedy International Airport",
                          "state": "New York",
                          "tz": "America/New_York"
                        }
                      ],
                      "count": 1
                    }
                  ]
                }
              }
            }
          },
          "400": {
            "description": "Bad request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Error"
                },
                "example": {
                  "error": {
                    "code": 4002,
                    "description": "Query parameter 'q' is required for airport search",
                    "message": "required request field is missing or wrong input"
                  }
                }
              }
            }
          },
          "401": {
            "description": "Unauthorized",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Error"
                },
                "example": {
                  "error": {
                    "code": 401,
                    "description": "Authentication failed",
                    "message": "unauthorized"
                  }
                }
              }
            }
          },
          "500": {
            "description": "Server error",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Error"
                },
                "example": {
                  "error": {
                    "code": 5000,
                    "description": "Failed to retrieve airports",
                    "message": "unable to process request"
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}
```