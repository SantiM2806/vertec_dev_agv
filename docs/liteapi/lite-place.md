---
updatedAt: 2026-06-02T09:22:54.000Z
agentTools:
  projectIndex: https://docs.liteapi.travel/llms.txt
---

# Search for a specific place

## Overview

Get detailed information about a specific place using its Place ID. Returns complete place details including boundaries and location information.

**Pricing**: $0.01 per request

## When to Use

- **Place details** - Get full information about a selected place
- **Boundary information** - Retrieve place boundaries for hotel searches
- **Location verification** - Verify place details before using in searches
- **Display information** - Show place names and addresses to users

## What You Get

- **Complete place details** - Full information about the place
- **Boundary data** - Geographic boundaries for the place
- **Location information** - Coordinates, address, and display name
- **Place metadata** - Types, formatted address, and language

## Quick Start

Provide the `placeId` in the URL path. Returns complete details for that specific place.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "title": "API: Hotel data",
    "version": "3.0.0"
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
  "paths": {
    "/data/places/{placeId}": {
      "get": {
        "tags": [
          "Places"
        ],
        "summary": "Search for a specific place",
        "description": "## Overview\n\nGet detailed information about a specific place using its Place ID. Returns complete place details including boundaries and location information.\n\n**Pricing**: $0.01 per request\n\n## When to Use\n\n- **Place details** - Get full information about a selected place\n- **Boundary information** - Retrieve place boundaries for hotel searches\n- **Location verification** - Verify place details before using in searches\n- **Display information** - Show place names and addresses to users\n\n## What You Get\n\n- **Complete place details** - Full information about the place\n- **Boundary data** - Geographic boundaries for the place\n- **Location information** - Coordinates, address, and display name\n- **Place metadata** - Types, formatted address, and language\n\n## Quick Start\n\nProvide the `placeId` in the URL path. Returns complete details for that specific place.",
        "security": [
          {
            "apikeyAuth": []
          }
        ],
        "parameters": [
          {
            "name": "placeId",
            "in": "path",
            "schema": {
              "type": "string"
            },
            "description": "Unique identifier of the place to retrieve.",
            "required": true,
            "example": "ChIJLc8UZFdqXz4ROODwebhx2rM"
          },
          {
            "name": "language",
            "in": "query",
            "schema": {
              "type": "string"
            },
            "description": "The language code, indicating in which language the results should be returned. e.g. 'en'",
            "example": "en"
          },
          {
            "name": "sessionId",
            "in": "query",
            "schema": {
              "type": "string"
            },
            "description": "Optional Google Places billing session ID. Pass the same value used in the preceding autocomplete calls to settle the billing session, making those autocomplete calls free. Can also be passed via the X-Places-Session-Id header (header takes priority).",
            "example": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "headers": {
              "Content-Type": {
                "schema": {
                  "type": "string",
                  "example": "application/json; charset=utf-8"
                }
              },
              "Content-Encoding": {
                "schema": {
                  "type": "string",
                  "example": "gzip"
                }
              }
            },
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "data": {
                      "type": "object",
                      "required": [
                        "displayName",
                        "location"
                      ],
                      "properties": {
                        "addressComponents": {
                          "type": "array",
                          "description": "A list of address components, each representing a part of the address such as country, city, or postal code.",
                          "items": {
                            "type": "object",
                            "properties": {
                              "languageCode": {
                                "type": "string",
                                "description": "The language code for the address component (e.g., 'en', 'en-US')."
                              },
                              "longText": {
                                "type": "string",
                                "description": "The full text of the address component (e.g., 'France')."
                              },
                              "shortText": {
                                "type": "string",
                                "description": "The abbreviated or short text for the address component (e.g., 'FR')."
                              },
                              "types": {
                                "type": "array",
                                "description": "A list of types describing the address component (e.g., 'country', 'political').",
                                "items": {
                                  "type": "string"
                                }
                              }
                            }
                          }
                        },
                        "location": {
                          "type": "object",
                          "description": "The geographic coordinates of the place.",
                          "properties": {
                            "latitude": {
                              "type": "number",
                              "description": "The latitude of the location."
                            },
                            "longitude": {
                              "type": "number",
                              "description": "The longitude of the location."
                            }
                          }
                        },
                        "viewport": {
                          "type": "object",
                          "description": "The recommended viewport for displaying the place, defined by high and low latitude/longitude bounds.",
                          "properties": {
                            "high": {
                              "type": "object",
                              "description": "The northeast corner of the viewport.",
                              "properties": {
                                "latitude": {
                                  "type": "number",
                                  "description": "The latitude of the high (northeast) corner."
                                },
                                "longitude": {
                                  "type": "number",
                                  "description": "The longitude of the high (northeast) corner."
                                }
                              }
                            },
                            "low": {
                              "type": "object",
                              "description": "The southwest corner of the viewport.",
                              "properties": {
                                "latitude": {
                                  "type": "number",
                                  "description": "The latitude of the low (southwest) corner."
                                },
                                "longitude": {
                                  "type": "number",
                                  "description": "The longitude of the low (southwest) corner."
                                }
                              }
                            }
                          }
                        },
                        "displayName": {
                          "type": "string",
                          "description": "The display name of the place (e.g., 'London Marriott Hotel Park Lane')."
                        },
                        "types": {
                          "type": "array",
                          "description": "A list of place types (e.g., 'lodging', 'hotel', 'establishment').",
                          "items": {
                            "type": "string"
                          }
                        }
                      }
                    }
                  }
                },
                "example": {
                  "data": {
                    "addressComponents": [
                      {
                        "languageCode": "en",
                        "longText": "140",
                        "shortText": "140",
                        "types": [
                          "street_number"
                        ]
                      },
                      {
                        "languageCode": "en",
                        "longText": "Park Lane",
                        "shortText": "Park Ln",
                        "types": [
                          "route"
                        ]
                      },
                      {
                        "languageCode": "en",
                        "longText": "London",
                        "shortText": "London",
                        "types": [
                          "postal_town"
                        ]
                      },
                      {
                        "languageCode": "en",
                        "longText": "Greater London",
                        "shortText": "Greater London",
                        "types": [
                          "administrative_area_level_2",
                          "political"
                        ]
                      },
                      {
                        "languageCode": "en",
                        "longText": "England",
                        "shortText": "England",
                        "types": [
                          "administrative_area_level_1",
                          "political"
                        ]
                      },
                      {
                        "languageCode": "en",
                        "longText": "United Kingdom",
                        "shortText": "GB",
                        "types": [
                          "country",
                          "political"
                        ]
                      },
                      {
                        "languageCode": "en",
                        "longText": "W1K 7AA",
                        "shortText": "W1K 7AA",
                        "types": [
                          "postal_code"
                        ]
                      }
                    ],
                    "displayName": "London Marriott Hotel Park Lane",
                    "location": {
                      "latitude": 51.513107299999994,
                      "longitude": -0.1577347
                    },
                    "types": [
                      "hotel",
                      "wedding_venue",
                      "event_venue",
                      "lodging",
                      "point_of_interest",
                      "establishment"
                    ],
                    "viewport": {
                      "high": {
                        "latitude": 51.614437080291495,
                        "longitude": -0.05660501970849796
                      },
                      "low": {
                        "latitude": 51.41173911970849,
                        "longitude": -0.259302980291502
                      }
                    }
                  }
                }
              }
            }
          },
          "400": {
            "description": "Bad request",
            "headers": {
              "Content-Type": {
                "schema": {
                  "type": "string",
                  "example": "application/json; charset=utf-8"
                }
              },
              "Content-Encoding": {
                "schema": {
                  "type": "string",
                  "example": "gzip"
                }
              }
            },
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "error": {
                      "type": "object",
                      "properties": {
                        "code": {
                          "type": "integer",
                          "description": "Numeric error code representing the type of issue"
                        },
                        "description": {
                          "type": "string",
                          "description": "Brief explanation of the error type"
                        },
                        "message": {
                          "type": "string",
                          "description": "Message providing context about the error"
                        }
                      }
                    }
                  }
                },
                "example": {
                  "value": {
                    "error": {
                      "code": 4003,
                      "description": "invalid place ID",
                      "message": "request field is invalid or wrong"
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  },
  "components": {
    "securitySchemes": {
      "apikeyAuth": {
        "name": "X-API-Key",
        "type": "apiKey",
        "in": "header"
      }
    }
  },
  "x-readme": {
    "explorer-enabled": true,
    "proxy-enabled": true,
    "samples-enabled": true
  }
}
```